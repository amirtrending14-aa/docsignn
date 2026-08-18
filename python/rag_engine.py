import chromadb
import os
import sys
import io
import json
import re
import requests
from docx import Document

VERSION = "2.3"  # CHANGED v2.3: исправлен критичный баг ("None" в тексте документа при
                  # explicit JSON null от фронтенда), дата теперь формируется ДЕТЕРМИНИРОВАННО
                  # кодом (не зависит от ИИ), fallback-тексты стали грамматически связными,
                  # добавлен явный сигнал в лог при ПОЛНОМ отказе ИИ (чтобы не терялось молча)

if sys.platform == 'win32':
    sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding='utf-8')
    sys.stderr = io.TextIOWrapper(sys.stderr.buffer, encoding='utf-8')

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
VECTOR_DB_PATH = os.path.join(BASE_DIR, '..', 'vector_db')
DOCS_PATH = os.path.join(BASE_DIR, 'my_documents')

SYNONYMS = {
    'ариза': ['дархост', 'ариза'], 'заявление': ['дархост', 'ариза', 'заявление'],
    'доверенность': ['ваколатнома', 'доверенность'], 'рецензия': ['тақриз', 'рецензия'],
    'приказ': ['фармон', 'приказ'], 'договор': ['шартнома', 'договор'],
    'справка': ['маълумотнома', 'справка'], 'ходатайство': ['илтимоснома', 'ходатайство'],
    'протокол': ['протокол'], 'акт': ['акт'], 'отчет': ['гузориш', 'отчёт'],
}

# ========== ТИПЫ ДОКУМЕНТОВ (выбирает КОД, не ИИ!) ==========
TYPE_MAP = [
    (['ариза', 'заявление'], 'АРИЗА'),
    (['гузориш', 'отчет', 'отчёт'], 'ГУЗОРИШ'),
    (['фармоиш', 'приказ'], 'ФАРМОИШ'),
    (['маълумотнома', 'справка'], 'МАЪЛУМОТНОМА'),
    (['тақриз', 'рецензия'], 'ТАҚРИЗ'),
    (['шартнома', 'договор'], 'ШАРТНОМА'),
    (['ваколатнома', 'доверенность'], 'ВАКОЛАТНОМА'),
    (['илтимоснома', 'ходатайство'], 'ИЛТИМОСНОМА'),
    (['барнома', 'программа'], 'БАРНОМА'),
    (['дастовар'], 'ДАСТОВАР'),
    (['протокол'], 'ПРОТОКОЛ'),
    (['акт'], 'АКТ'),
]


def normalize_type(raw):
    t = str(raw or '').lower()
    for keys, title in TYPE_MAP:
        for k in keys:
            if re.search(r'\b' + re.escape(k) + r'\b', t):
                log('normalize_type: raw="' + str(raw) + '" -> ' + title + ' (совпало по слову "' + k + '")')
                return title
    result = str(raw or 'ДОКУМЕНТ').upper()
    log('normalize_type: raw="' + str(raw) + '" -> ' + result + ' (совпадений в TYPE_MAP не найдено, беру как есть)')
    return result


def log(msg):
    print(msg, file=sys.stderr)


# CHANGED v2.3: КРИТИЧНЫЙ БАГ был в том, что везде использовалось data.get('key', ''),
# а это НЕ подставляет '' если ключ ЕСТЬ, но его значение явно null (Python None) —
# именно так JSON от PHP/Laravel часто присылает пустые поля. В итоге str(None) → "None"
# утекало прямо в текст документа. safe() чинит это раз и навсегда для всего файла.
def safe(data, key, default=''):
    v = data.get(key, default)
    return default if v is None else v


# CHANGED v2.3: дата теперь собирается КОДОМ, а не ИИ — так она гарантированно на
# литературном таджикском, даже если Groq API недоступен и сработал fallback.
MONTHS_TJ = {
    '1': 'январи', 'январь': 'январи', 'january': 'январи', 'январи': 'январи',
    '2': 'феврали', 'февраль': 'феврали', 'february': 'феврали', 'феврали': 'феврали',
    '3': 'марти', 'март': 'марти', 'march': 'марти', 'марти': 'марти',
    '4': 'апрели', 'апрель': 'апрели', 'april': 'апрели', 'апрели': 'апрели',
    '5': 'майи', 'май': 'майи', 'may': 'майи', 'майи': 'майи',
    '6': 'июни', 'июнь': 'июни', 'june': 'июни', 'июни': 'июни',
    '7': 'июли', 'июль': 'июли', 'july': 'июли', 'июли': 'июли',
    '8': 'августи', 'август': 'августи', 'august': 'августи', 'августи': 'августи',
    '9': 'сентябри', 'сентябрь': 'сентябри', 'september': 'сентябри', 'сентябри': 'сентябри',
    '10': 'октябри', 'октябрь': 'октябри', 'october': 'октябри', 'октябри': 'октябри',
    '11': 'ноябри', 'ноябрь': 'ноябри', 'november': 'ноябри', 'ноябри': 'ноябри',
    '12': 'декабри', 'декабрь': 'декабри', 'december': 'декабри', 'декабри': 'декабри',
}


def format_tajik_date(day, month, year):
    day = str(day or '').strip()
    month_key = str(month or '').strip().lower()
    month_tj = MONTHS_TJ.get(month_key, month_key)  # если формат незнакомый — берём как есть
    year = str(year or '').strip()
    parts = [p for p in [day, month_tj, ('соли ' + year) if year else ''] if p]
    return ' '.join(parts)


def expand_query(query):
    q = query.lower()
    extra = []
    for ru, words in SYNONYMS.items():
        if ru in q:
            extra.extend(words)
    return query + ' ' + ' '.join(extra)


# ========== ЧТЕНИЕ ФАЙЛОВ ==========
def extract_text_from_docx(filepath, marked=False):
    doc = Document(filepath)
    parts = []
    for i, p in enumerate(doc.paragraphs, 1):
        if p.text.strip():
            parts.append(('[абз. ' + str(i) + '] ' if marked else '') + p.text.strip())
    return '\n'.join(parts)


def extract_text_from_pdf(filepath, marked=False):
    from pypdf import PdfReader
    reader = PdfReader(filepath)
    parts = []
    for i, page in enumerate(reader.pages, 1):
        t = page.extract_text()
        if t and t.strip():
            parts.append(('[стр. ' + str(i) + '] ' if marked else '') + t.strip())
    return '\n'.join(parts)


def extract_text(filepath, marked=False):
    ext = os.path.splitext(filepath)[1].lower()
    if ext == '.docx':
        return extract_text_from_docx(filepath, marked)
    if ext == '.pdf':
        return extract_text_from_pdf(filepath, marked)
    return ''


def split_into_chunks(text, chunk_size=400, overlap=80):
    words = text.split()
    chunks, start = [], 0
    while start < len(words):
        chunks.append(' '.join(words[start:start + chunk_size]))
        start += chunk_size - overlap
    return chunks


def get_all_documents():
    os.makedirs(DOCS_PATH, exist_ok=True)
    docs = []
    for filename in sorted(os.listdir(DOCS_PATH)):
        filepath = os.path.join(DOCS_PATH, filename)
        if not os.path.isfile(filepath):
            continue
        try:
            text = extract_text(filepath)
        except Exception as e:
            log('Ошибка чтения ' + filename + ': ' + str(e))
            text = ''
        if text:
            docs.append({'filename': filename, 'text': text})
    log('Всего файлов в базе: ' + str(len(docs)))
    return docs


def init_vector_db():
    docs = get_all_documents()
    client = chromadb.PersistentClient(path=VECTOR_DB_PATH)
    try:
        client.delete_collection("contracts")
    except Exception:
        pass
    collection = client.create_collection(name="contracts", metadata={"hnsw:space": "cosine"})
    chunks, metas, ids = [], [], []
    for doc in docs:
        for i, ch in enumerate(split_into_chunks(doc['text'])):
            chunks.append(ch)
            metas.append({"source": doc['filename']})
            ids.append(doc['filename'] + '_' + str(i))
    if chunks:
        collection.add(documents=chunks, metadatas=metas, ids=ids)
        log('Vector DB: ' + str(len(chunks)) + ' chunks')
    return collection


def detect_best_document(query):
    query_exp = expand_query(query)
    client = chromadb.PersistentClient(path=VECTOR_DB_PATH)
    try:
        collection = client.get_collection("contracts")
    except Exception:
        collection = init_vector_db()

    sem = {}
    try:
        res = collection.query(query_texts=[query_exp], n_results=10)
        if res['metadatas'] and res['metadatas'][0]:
            for meta, dist in zip(res['metadatas'][0], res['distances'][0]):
                sem[meta['source']] = sem.get(meta['source'], 0) + 1 / (1 + dist)
    except Exception as e:
        log('Search error: ' + str(e))

    docs = get_all_documents()
    q_words = set(w for w in re.findall(r'[a-zа-яёҳҷқғӣӯф]+', query_exp.lower()) if len(w) > 3)
    kw = {}
    for d in docs:
        t, fn = d['text'].lower(), d['filename'].lower()
        s = sum(1 for w in q_words if w in t) + sum(2 for w in q_words if w in fn)
        kw[d['filename']] = s

    if not sem and not kw:
        return None

    def norm(dd):
        m = max(dd.values()) if dd and max(dd.values()) > 0 else 1
        return {k: v / m for k, v in dd.items()}

    sem_n, kw_n = norm(sem), norm(kw)
    combined = {f: sem_n.get(f, 0) + kw_n.get(f, 0) * 0.8 for f in set(sem_n) | set(kw_n)}
    best = max(combined, key=combined.get)
    log('Выбран файл: ' + best + ' (score ' + str(round(combined[best], 2)) + ')')
    return best


def get_document_text(filename, marked=False):
    filepath = os.path.join(DOCS_PATH, filename)
    if os.path.exists(filepath):
        try:
            return extract_text(filepath, marked)
        except Exception as e:
            log('Read error: ' + str(e))
    return ''


# ========== GROQ ==========
def get_api_key():
    key = os.environ.get('GROQ_API_KEY', '').strip().strip('"').strip("'").strip()
    if key:
        return key
    env_path = os.path.join(BASE_DIR, '..', '.env')
    if os.path.exists(env_path):
        with open(env_path, encoding='utf-8') as f:
            for line in f:
                line = line.strip()
                if line.startswith('GROQ_API_KEY='):
                    return line.split('=', 1)[1].strip().strip('"').strip("'")
    return ''


def call_groq(prompt, system, max_tokens=4096, json_mode=False, temperature=0.3):
    api_key = get_api_key()
    if not api_key:
        log('⚠️ GROQ_API_KEY не найден!')
        return None
    
    log(f'🤖 Вызов Groq, модель: openai/gpt-oss-120b')
    
    try:
        payload = {
            "model": "openai/gpt-oss-120b",
            "messages": [
                {"role": "system", "content": system},
                {"role": "user", "content": prompt[:10000]},  # ограничение для лога
            ],
            "temperature": temperature,
            "max_tokens": max_tokens,
        }
        if json_mode:
            payload["response_format"] = {"type": "json_object"}
        
        r = requests.post(
            "https://api.groq.com/openai/v1/chat/completions",
            headers={"Authorization": "Bearer " + api_key, "Content-Type": "application/json"},
            json=payload, timeout=180,
        )
        
        log(f'📡 Groq response: {r.status_code}')
        
        if r.status_code != 200:
            log('❌ API error ' + str(r.status_code) + ': ' + r.text[:500])
            return None
        
        result = r.json()['choices'][0]['message']['content'].strip()
        log(f'✅ Получен ответ длиной {len(result)} символов')
        return result
        
    except Exception as e:
        log('❌ AI error: ' + str(e))
        return None
def parse_json_safe(content):
    if not content:
        return None
    content = re.sub(r'^```(json)?', '', content.strip())
    content = re.sub(r'```$', '', content.strip())
    m = re.search(r'\{.*\}', content, re.S)
    if m:
        try:
            return json.loads(m.group(0))
        except Exception:
            return None
    return None


# ================================================================
# ВСПОМОГАТЕЛЬНОЕ: извлечение "смысловых слов" пользователя для
# программной проверки (не даём ИИ, а проверяем ЕГО ответ этим кодом)
# CHANGED v2.2: раньше сохранность смысла проверялась ТОЛЬКО одним
# примером внутри промпта ("муаллим"/"мудир"). Теперь код сам вытаскивает
# значимые слова из сырого ввода и после ответа ИИ логирует, какие из них
# пропали — это работает для ЛЮБЫХ слов, а не только для примера в промпте.
# ================================================================
STOPWORDS = {
    'ва', 'бо', 'дар', 'аз', 'ба', 'ки', 'ин', 'он', 'барои', 'то', 'ман', 'шумо',
    'мо', 'онҳо', 'ин', 'хеле', 'ҳам', 'ё', 'ҳам', 'the', 'and', 'or', 'is', 'for',
}


def extract_key_terms(*texts):
    terms = set()
    for text in texts:
        for w in re.findall(r'[a-zа-яёҳҷқғӣӯф\-]{4,}', str(text or '').lower()):
            if w not in STOPWORDS:
                terms.add(w)
    return terms


def warn_if_terms_dropped(before_terms, after_text, label):
    """Не блокирует, только логирует — чтобы было видно в логах, если ИИ
    незаметно подменил слово другим по смыслу (частая беда LLM-корректоров)."""
    after_norm = str(after_text or '').lower()
    # ищем не точное слово (могла измениться орфография), а "похожий" по
    # первым 4 буквам корень — этого достаточно, чтобы поймать грубую подмену
    dropped = []
    for term in before_terms:
        root = term[:4]
        if root not in after_norm:
            dropped.append(term)
    if dropped:
        log('⚠️ ' + label + ': возможна потеря смысловых слов: ' + ', '.join(sorted(dropped)[:10]))


# ================================================================
# ШАГ 1: ПОНИМАЛКА-ИСПРАВИТЕЛЬ (строгая, НЕ меняет смысл)
# CHANGED v2.2: правило "не подменяй слова" было проиллюстрировано ОДНИМ
# частным примером ("муаллим"/"мудир"), из-за чего модель могла решить,
# что это правило касается только этой пары слов. Теперь правило
# сформулировано как ОБЩИЙ принцип: любое смысловое слово (должность,
# место, статус, предмет, действие) исправляется ТОЛЬКО орфографически,
# пример дан как иллюстрация метода, а не как список разрешённых слов.
# ================================================================
def clean_and_correct_input(data):
    fixed_type = normalize_type(safe(data, 'doc_type'))
    tajik_date = format_tajik_date(safe(data, 'day'), safe(data, 'month'), safe(data, 'year'))
    raw = json.dumps({
        "to_whom": str(safe(data, 'to_whom')),
        "from_whom": str(safe(data, 'from_whom')),
        "from_details": str(safe(data, 'from_details')),
        "city": str(safe(data, 'city')),
        "date": tajik_date,
        "subject": str(safe(data, 'subject')),
        "extra": str(safe(data, 'extra')),
        "answers": [str(a) for a in (data.get('answers') or []) if str(a).strip()],
    }, ensure_ascii=False)

    prompt = (
        "Ты — корректор литературного таджикского языка.\n"
        "Пользователь заполнил анкету с ошибками, возможно латиницей или по-русски.\n\n"
        "=== СЫРАЯ АНКЕТА ===\n" + raw + "\n=== КОНЕЦ ===\n\n"
        "Тип документа УЖЕ известен: " + fixed_type + ". Не выдумывай другие типы!\n\n"
        "ГЛАВНОЕ ПРАВИЛО — ТЫ КОРРЕКТОР, А НЕ РЕДАКТОР СМЫСЛА:\n"
        "Для КАЖДОГО смыслового слова анкеты — любая должность, статус, место, "
        "учреждение, предмет, действие, которое написал пользователь, — сохраняй "
        "именно ЕГО значение. Тебе разрешено:\n"
        "  а) исправить орфографическую ошибку САМОГО этого слова (не заменять его "
        "другим словом с похожим, но иным значением);\n"
        "  б) перевести слово с латиницы/русского на литературную таджикскую кириллицу, "
        "сохраняя его буквальное значение (не 'более уместный' синоним, а прямой перевод).\n"
        "ЗАПРЕЩЕНО заменять одно понятие другим из той же смысловой области, даже если "
        "оно кажется 'более уместным в контексте' — например, если пользователь указал "
        "конкретную должность, место или предмет, оставь именно его, а не близкое по "
        "смыслу слово. Правило действует на ЛЮБОЕ слово анкеты, а не только на примеры "
        "из инструкции.\n"
        "Пример метода (не список разрешённых слов, а иллюстрация принципа): если написано "
        "«мехоцам» — это опечатка слова «мехоҳам», исправь орфографию; но если написано "
        "«муаллим», не превращай его в «мудир» или любое другое слово другой должности — "
        "и то же самое применяй ко ВСЕМ остальным словам анкеты, какими бы они ни были.\n\n"
        "ОСТАЛЬНЫЕ ПРАВИЛА:\n"
        "2. Конвертируй в таджикскую кириллицу:\n"
        "   - латиница: khujand → Хуҷанд;\n"
        "   - русизмы: худжанд → Хуҷанд, улица → кӯча, город → шаҳр, компания → ширкат;\n"
        "   - орфографические ошибки: мехоцам → мехоҳам, хохиш → хоҳиш, диҳод → диҳед.\n"
        "3. Поля answers И extra ОБА ВЛЕЙ в subject — это не мусор, а конкретные факты "
        "(навыки, причины, предложения), они ОБЯЗАНЫ попасть в итоговый subject.\n"
        "4. Поле date УЖЕ готово (\"" + tajik_date + "\") — верни его БЕЗ ИЗМЕНЕНИЙ, "
        "ничего не досочиняй и не переформатируй.\n"
        "5. subject должен быть НЕ пересказом в 1-2 словах, а РАЗВЁРНУТЫМ изложением "
        "(3-6 полных предложений на литературном таджикском): если пользователь написал "
        "коротко, восстанови и явно распиши стандартные для этого типа документа детали "
        "(цель обращения, обоснование, конкретные факты из answers/extra) — но НЕ выдумывай "
        "факты, которых пользователь не называл (даты, имена, цифры, причины).\n\n"
        "Верни СТРОГО JSON (без markdown):\n"
        '{\n'
        '  "to_whom": "кому, литературный таджикский, сохранив смысл",\n'
        '  "from_whom": "от кого, правильно",\n'
        '  "from_details": "адрес/контакты, правильно",\n'
        '  "city": "город",\n'
        '  "date": "' + tajik_date + '",\n'
        '  "subject": "развёрнутая суть, включая ВСЕ факты из subject+answers+extra, '
        '3-6 предложений, литературный таджикский"\n'
        '}'
    )

    content = call_groq(prompt, "Ты корректор таджикского. Возвращай ТОЛЬКО валидный JSON.",
                        max_tokens=2048, json_mode=True, temperature=0.2)
    parsed = parse_json_safe(content)
    if not parsed:
        # CHANGED v2.3: ЭТО ЗНАЧИТ, ЧТО ИИ НЕ ОТВЕТИЛ (нет ключа GROQ_API_KEY, сеть,
        # лимиты и т.п.) — раньше это тонуло молча и результат выглядел как "сырой,
        # неисправленный текст". Делаем это ГРОМКИМ в логах, чтобы сразу было видно.
        log('⚠️⚠️⚠️ ИИ НЕ ОТВЕТИЛ на clean_and_correct_input — текст пойдёт БЕЗ исправления '
            'орфографии/расширения. Проверь GROQ_API_KEY и доступность api.groq.com в логах выше.')
        fallback_subject = str(safe(data, 'subject'))
        extra_val = str(safe(data, 'extra')).strip()
        if extra_val:
            fallback_subject += ' ' + extra_val
        parsed = {
            "to_whom": safe(data, 'to_whom'),
            "from_whom": safe(data, 'from_whom'),
            "from_details": safe(data, 'from_details'),
            "city": safe(data, 'city'),
            "date": tajik_date,
            "subject": fallback_subject,
        }
    parsed['doc_type'] = fixed_type  # код решает тип, не ИИ

    # CHANGED v2.2: программная проверка сохранности смысла — работает для
    # ЛЮБЫХ слов пользователя, не только для примера из промпта
    before = extract_key_terms(data.get('to_whom'), data.get('from_whom'), data.get('subject'), data.get('extra'))
    after = ' '.join(str(v) for v in parsed.values())
    warn_if_terms_dropped(before, after, 'clean_and_correct_input')

    return parsed


# ================================================================
# ШАГ 2: ВОПРОСЫ ЗАДАЁТ КОД (не ИИ!) — всегда по делу, по-таджикски
# ================================================================
def ask_smart_questions(cleaned_data):
    questions = []

    def empty(v):
        return len(str(v or '').strip()) < 3

    if empty(cleaned_data.get('to_whom')):
        questions.append("Ба кӣ муроҷиат мекунед? Номи ташкилот ва вазифаи шахсро нависед (масалан: «Ба директори мактаби №12»).")
    if empty(cleaned_data.get('from_whom')):
        questions.append("Номи пурраи худро нависед (масалан: «Аминов Амир»).")
    if empty(cleaned_data.get('from_details')):
        questions.append("Суроға ва рақами телефони худро нависед (масалан: «ш. Хуҷанд, кӯчаи Р. Аюб, х. 44, тел. +992 90 000-00-00»).")
    if len(str(cleaned_data.get('subject') or '').strip()) < 20:
        questions.append("Мақсади муроҷиатро муфассалтар нависед: чӣ мехоҳед ва барои чӣ (1–2 ҷумла).")

    return questions[:2]


# ================================================================
# ШАГ 3: ГЕНЕРАЦИЯ СТРУКТУРЫ (с идеальным примером)
# CHANGED v2.2: правило "не меняй смысл адресата" тоже было привязано к
# частному примеру муаллим/мудир — обобщено на все роли/статусы/понятия.
# ================================================================
def _call_generate(cleaned_data, sample_text, fixed_title, retry_note=''):
    sample_part = ""
    if sample_text:
        sample_part = ("\n=== ОБРАЗЕЦ (учитель СТИЛЯ и ФОРМАТА, не переписывай его смысл, "
                       "НЕ копируй имена/числа/даты!) ===\n"
                       + sample_text[:4000] + "\n=== КОНЕЦ ОБРАЗЦА ===\n")

    perfect_example = (
        "\n=== НАМУНАИ ФОРМАТӢ (только формат/структура, НЕ содержание — "
        "содержание всегда берётся из ДАННЫХ конкретного случая) ===\n"
        '{\n'
        '  "header": ["Ба мудири мактаби №12-и ш. Хуҷанд", "аз Амиров Амир, сокини ш. Хуҷанд", "кӯчаи Р. Аюб, х. 44, тел. +992 90 000-00-00"],\n'
        '  "title": "АРИЗА",\n'
        '  "body": ["Мӯҳтарам мудир,", "Ман, Амиров Амир, бо арзи эҳтиром ба Шумо муроҷиат менамоям ва хоҳишмандам, ки маро ба мактаби шумо қабул намоед, зеро ман дар фанни математика ва информатика натиҷаҳои хуб дорам ва дар олимпиадаҳои шаҳрӣ иштирок кардаам.", "Хоҳишмандам, ки дархости маро дида баромада, қарори мусбат қабул фармоед.", "Пешаки аз таваҷҷӯҳи Шумо миннатдорам."],\n'
        '  "signature": ["Бо эҳтиром,", "Амиров Амир", "16 августи соли 2026"]\n'
        '}\n'
        "=== КОНЕЦ ПРИМЕРА (это ТОЛЬКО демонстрация формата — должность, имена, "
        "школа, предмет здесь ПРИДУМАНЫ для примера, в реальном документе бери их "
        "ИСКЛЮЧИТЕЛЬНО из ДАННЫХ ниже) ===\n"
    )

    prompt = (
        "Ты — юрист-документовед Таджикистана, 20 лет стажа.\n"
        "Составь НОВЫЙ официальный документ на литературном таджикском.\n\n"
        "=== ДАННЫЕ (факты ТОЛЬКО отсюда, но ИСПОЛЬЗУЙ ИХ ВСЕ до единого) ===\n"
        + json.dumps(cleaned_data, ensure_ascii=False) + "\n=== КОНЕЦ ===\n"
        + sample_part + perfect_example +
        "\nПРАВИЛА:\n"
        "1. title УЖЕ выбран: \"" + fixed_title + "\". Верни его в поле title ТОЧНО так, "
        "НЕ выдумывай слов («АРИЗАНОМА» не существует!).\n"
        "2. НЕ подменяй ни одно смысловое слово из ДАННЫХ (должность/статус адресата, "
        "название места, учреждения, предмета и т.п.) другим словом с иным значением — "
        "какое слово указано в данных, такое и используй, только в грамматически верной "
        "форме. Это касается ВСЕХ понятий документа, а не только адресата.\n"
        "3. Формулы: «Бо арзи эҳтиром муроҷиат менамоям», «Хоҳишмандам, ки...», "
        "«Пешаки аз таваҷҷӯҳи Шумо миннатдорам».\n"
        "4. ЗАПРЕЩЕНО: «Ман хастам, ки...», «арз эҳтиром» (правильно: «арзи эҳтиром»), смешение лиц.\n"
        "5. body: 3–4 абзаца, СУММАРНО 120–250 слов. Короткая записка = ОШИБКА.\n"
        "6. КАЖДЫЙ конкретный факт из subject (навыки, причины, предложения, детали) ДОЛЖЕН "
        "явно появиться в body — перечисли их, а не сожми в одну общую фразу вроде "
        "«хеле маҳоратмандам». Пример: если в данных сказано, что человек умеет делать сайты — "
        "так и напиши это отдельным предложением, не обобщай.\n"
        "7. ЗАПРЕЩЕНО писать шаблонные фразы без опоры на факты — каждое предложение body "
        "должно нести НОВУЮ информацию из данных, а не повторять предыдущее другими словами.\n"
        "8. Нет данных для какого-то поля — «____________», но НЕ выдумывай факты, которых нет.\n"
        + (retry_note + "\n" if retry_note else "") +
        "\nВерни СТРОГО JSON структуры: {header: [...], title, body: [...], signature: [3 строки]}."
    )

    content = call_groq(prompt, "Ты делопроизводитель РТ. Возвращай ТОЛЬКО валидный JSON.",
                        max_tokens=4096, json_mode=True, temperature=0.4)
    return parse_json_safe(content)


def generate_document_structure(cleaned_data, sample_text, fixed_title, retry_note=''):
    structure = None
    note = retry_note
    for attempt in range(1, 4):
        structure = _call_generate(cleaned_data, sample_text, fixed_title, note)
        if structure and isinstance(structure.get('body'), list):
            words = sum(len(str(b).split()) for b in structure['body'])
            if words >= 80:
                if attempt > 1:
                    log('Generation OK on attempt ' + str(attempt) + ' (' + str(words) + ' words)')
                # CHANGED v2.2: программная проверка сохранности смысла и здесь
                before = extract_key_terms(cleaned_data.get('to_whom'), cleaned_data.get('from_whom'),
                                            cleaned_data.get('subject'))
                after = ' '.join(str(b) for b in structure['body']) + ' ' + ' '.join(str(h) for h in structure.get('header', []))
                warn_if_terms_dropped(before, after, 'generate_document_structure')
                return structure
            log('Attempt ' + str(attempt) + ': body too short (' + str(words) + ' words), retrying...')
            note = ("ПРОШЛАЯ ПОПЫТКА БЫЛА СЛИШКОМ КОРОТКОЙ И ОБЩЕЙ (" + str(words) + " слов). "
                    "Пиши 120–250 слов, раскрывая КАЖДЫЙ факт из данных отдельным предложением.")
        else:
            log('Attempt ' + str(attempt) + ': generation failed, retrying...')

    if not structure or not isinstance(structure.get('body'), list):
        # CHANGED v2.3: раньше fallback просто вставлял subject как есть — если ИИ уже
        # на шаге 1 тоже не ответил, subject оставался сырым, неисправленным текстом
        # пользователя (отсюда "мехохам ... хохам кабул шуд" один в один). Теперь
        # fallback хотя бы оборачивает его в грамматически верную рамку официального
        # обращения — это не заменяет ИИ-расширение, но не выглядит как сломанный текст.
        log('⚠️⚠️⚠️ ИИ НЕ ОТВЕТИЛ на generate_document_structure после 3 попыток — документ '
            'собран по резервному шаблону БЕЗ расширения содержания. Проверь GROQ_API_KEY '
            'и доступность api.groq.com в логах выше (ищи "API error" / "AI error").')
        subj = str(safe(cleaned_data, 'subject')).strip() or '____________'
        from_whom = str(safe(cleaned_data, 'from_whom')).strip()
        fallback_body = [
            "Мӯҳтарам,",
            ("Ман, " + from_whom + ", бо арзи эҳтиром ба Шумо муроҷиат намуда, "
             "хоҳишмандам зерин масъаларо ба инобат гиред: " + subj)
            if from_whom else ("Бо арзи эҳтиром муроҷиат намуда, хоҳишмандам: " + subj),
            "Хоҳишмандам, ки дархости маро дида баромада, қарори мусбат қабул фармоед.",
            "Пешаки аз таваҷҷӯҳи Шумо миннатдорам.",
        ]
        structure = {
            "header": [str(safe(cleaned_data, 'to_whom')),
                       'аз ' + from_whom if from_whom else '',
                       str(safe(cleaned_data, 'from_details'))],
            "title": fixed_title,
            "body": fallback_body,
            "signature": ["Бо эҳтиром,", from_whom, str(safe(cleaned_data, 'date'))],
        }
    return structure


# ================================================================
# ШАГ 4: ПОЛИРОВКА + ЖЕЛЕЗНЫЙ КОНТРОЛЬ КОДОМ
# CHANGED v2.2: снова обобщено правило "не подменяй смысл" — раньше был
# частный пример (муаллим ≠ мудир) в скобках, теперь общий запрет на
# любую смысловую подмену любых слов.
# ================================================================
def polish_document(structure):
    prompt = (
        "Ты — СТРОГИЙ корректор литературного таджикского языка, 20 лет стажа.\n"
        "Исправь ВСЕ ошибки в структуре официального документа:\n\n"
        + json.dumps(structure, ensure_ascii=False) + "\n\n"
        "ЧЕК-ЛИСТ (исправь каждое нарушение):\n"
        "1. Орфография: ҳ, ҷ, қ, ғ, ӣ, ӯ («хоҳиш» НЕ «хохиш», «эҳтиром» НЕ «эхтиром»).\n"
        "2. Запятые: между однородными членами ОБЯЗАТЕЛЬНА: «орзуҳо, ҳоҳишҳои».\n"
        "3. Грамматика: «то ман метавонам» → «то тавонам»; «амалӣ намоям» → «амалӣ гардонам».\n"
        "4. Логика фраз: каждое предложение должно иметь смысл.\n"
        "   ПЛОХО: «Ман дар шаҳри Хуҷанд ифтихор мекунам, ки дар мактаби Шумо таҳсил намоям».\n"
        "   ХОРОШО: «Барои ман ифтихор аст, ки дар мактаби Шумо таҳсил мекунам».\n"
        "5. Официальный стиль, без русизмов, одно лицо («Ман»).\n"
        "6. НЕ меняй title. НЕ заменяй НИ ОДНО смысловое слово (должность, статус, "
        "название места/учреждения/предмета и т.д. — любое, а не какой-то конкретный "
        "пример) другим словом с иным значением: правь только орфографию и грамматическую "
        "форму слова, само значение должно остаться прежним. НЕ сокращай текст и НЕ убирай "
        "конкретные факты (навыки, причины, детали) — только грамматика и орфография.\n"
        "7. signature: ровно 3 строки — «Бо эҳтиром,», имя, дата.\n\n"
        "Верни СТРОГО JSON той же структуры, только с исправлениями."
    )
    content = call_groq(prompt, "Ты строгий корректор. Возвращай ТОЛЬКО JSON.",
                        max_tokens=4096, json_mode=True, temperature=0.2)
    result = parse_json_safe(content) or structure

    # CHANGED v2.2: проверка и после полировки
    before_text = ' '.join(str(b) for b in structure.get('body', [])) + ' ' + ' '.join(str(h) for h in structure.get('header', []))
    before = extract_key_terms(before_text)
    after_text = ' '.join(str(b) for b in result.get('body', [])) + ' ' + ' '.join(str(h) for h in result.get('header', []))
    warn_if_terms_dropped(before, after_text, 'polish_document')

    return result


def enforce_structure(structure, cleaned, fixed_title):
    """Код гарантирует: название, шапка, подпись — всегда правильные"""
    if not isinstance(structure, dict):
        structure = {}
    header = [str(x).strip() for x in structure.get('header', []) if str(x).strip()]
    body = [str(x).strip() for x in structure.get('body', []) if str(x).strip()]
    sig = [str(x).strip() for x in structure.get('signature', []) if str(x).strip()]

    to_whom = str(cleaned.get('to_whom', '')).strip()
    from_whom = str(cleaned.get('from_whom', '')).strip()
    from_details = str(cleaned.get('from_details', '')).strip()

    if not header:
        header = [to_whom, ('аз ' + from_whom) if from_whom else '', from_details]
        header = [h for h in header if h]

    if not body:
        body = [str(cleaned.get('subject', ''))]

    if not sig:
        sig = ['Бо эҳтиром,', from_whom, str(cleaned.get('date', ''))]
    else:
        if not any('эҳтиром' in s.lower() for s in sig):
            sig = ['Бо эҳтиром,'] + sig
        if from_whom and not any(from_whom.split()[0] in s for s in sig):
            sig.append(from_whom)
        if len(sig) < 3:
            sig.append(str(cleaned.get('date', '')))

    structure['header'] = header[:4]
    structure['title'] = fixed_title  # КОД решает название
    structure['body'] = body
    structure['signature'] = sig[:4]
    return structure


# ========== ЧАТ И АНАЛИЗ (без изменений) ==========
def answer_question(filename, question):
    text = get_document_text(filename, marked=True)
    if not text:
        return 'Не смог прочитать файл ' + str(filename)
    prompt = (
        "Вот документ с пометками [стр. N] / [абз. N].\n\n" + text[:25000] + "\n\n"
        "Вопрос: " + str(question) + "\n\n"
        "Ответь кратко, опираясь ТОЛЬКО на документ. Укажи источник: [стр. 2] или [абз. 5]."
    )
    return call_groq(prompt, "Ты помощник. Отвечай точно.", 1024) or 'ИИ недоступен.'


def summarize_document(text):
    prompt = (
        "Ты — аналитик документов.\n\n" + text[:30000] + "\n\n"
        "Сделай разбор:\n1. 📋 СОДЕРЖАНИЕ (3-5 предложений).\n2. 🔑 ГЛАВНЫЕ ПУНКТЫ.\n"
        "3. 📅 ВАЖНЫЕ ДАННЫЕ.\n4. ⚠️ РИСКИ. Пиши на языке документа."
    )
    return call_groq(prompt, "Ты аналитик.", 2048) or 'ИИ недоступен.'