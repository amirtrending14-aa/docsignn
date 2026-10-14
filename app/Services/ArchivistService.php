<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ArchivistService
{
    private const SIGNED_STATUSES   = ['signed', 'completed', 'approved', 'archive'];
    private const REJECTED_STATUSES = ['rejected', 'declined', 'failed'];
    private const FINISHED_STATUSES = ['signed', 'completed', 'approved', 'rejected', 'declined', 'failed', 'archive'];

    private const FORMAT_GROUPS = [
        'pdf' => ['pdf'], 
        'doc' => ['doc'], 
        'docx' => ['docx'], 
        'word' => ['doc', 'docx'],
        'xls' => ['xls'], 
        'xlsx' => ['xlsx'], 
        'excel' => ['xls', 'xlsx'], 
        'rtf' => ['rtf'],
    ];

    private const TYPE_KEYWORDS = [
        'договор' => ['договор', 'договоры', 'contract', 'шартнома', 'соглашение', 'контракт'],
        'аренда' => ['аренд', 'иҷора', 'наём'],
        'заявление' => ['заявлен', 'ариза', 'ариз', 'обращение'],
        'счёт' => ['счёт', 'счет', 'счета', 'инвоис', 'invoice', 'ҳисоб', 'фактура'],
        'доверенность' => ['доверенност', 'бозаргонӣ', 'полномочие'],
        'приказ' => ['приказ', 'приказы', 'фармон', 'распоряжение'],
        'справка' => ['справк', 'справки', 'маълумотнома'],
        'акт' => ['акт', 'акты', 'санҷиш'],
        'тз' => ['техническое задание', 'техзадание', 'ТЗ', 'тз', 'specification'],
        'отпуск' => ['отпуск', 'рухсатӣ', 'рухсати', 'рухсат'],
        'больничный' => ['больничн', 'маълумотномаи беморӣ', 'лист нетрудоспособности'],
        'командировка' => ['командиров', 'сафарӣ', 'поездка'],
        'автобиография' => ['автобиограф', 'тарҷумаи ҳол', 'биография'],
        'описание' => ['описани', 'формализованное описание', 'тавсиф'],
    ];

    private const QUERY_STOP_WORDS = [
        'а', 'без', 'бы', 'были', 'был', 'быть', 'в', 'вам', 'вас', 'весь', 'во', 'вот',
        'все', 'всех', 'вы', 'где', 'да', 'для', 'до', 'его', 'ее', 'если', 'есть', 'же',
        'за', 'и', 'из', 'или', 'им', 'их', 'как', 'какая', 'какие', 'какой', 'какого',
        'какое', 'когда', 'кого', 'кому', 'которого', 'которые', 'который', 'ли', 'мне',
        'можно', 'мой', 'моя', 'мои', 'моих', 'моем', 'моём', 'моими', 'мы', 'на', 'надо',
        'найди', 'найти', 'нам', 'наш', 'не', 'него', 'нее', 'нет', 'ни', 'них', 'но', 'ну',
        'о', 'об', 'один', 'она', 'они', 'оно', 'от', 'по', 'под', 'покажи', 'после', 'при',
        'про', 'пусть', 'с', 'сам', 'свой', 'сделай', 'со', 'так', 'там', 'те', 'тем', 'то',
        'только', 'том', 'ты', 'у', 'уже', 'что', 'чтобы', 'эта', 'эти', 'это', 'этого',
        'этой', 'этот', 'я', 'всего', 'архиве', 'архив', 'созданные', 'отправлял',
        'ҳама', 'дар', 'барои', 'бо', 'аз', 'ба', 'ман', 'мо', 'шумо', 'ки', 'чӣ', 'ин', 'он', 'ҳар', 'як',
        'document', 'documents', 'file', 'files', 'archive', 'archives', 'and', 'are', 'can', 'for', 'find', 'from', 'give', 'have', 'how', 'in', 'is', 'me', 'my', 'of', 'on', 'please', 'show', 'the', 'to', 'what', 'when', 'where', 'which', 'who', 'with', 'all', 'every', 'each',
        'документ', 'документы', 'документа', 'документе', 'документом', 'документов', 'документам', 'документами', 'документах',
        'файл', 'файлы', 'файла', 'файле', 'файлом', 'файлов', 'файлам', 'файлами',
        'ҳуҷҷат', 'ҳуҷҷатҳо', 'ҳуҷҷати', 'ҳуҷҷатро', 'бойгонӣ', 'маълумот',
        // Опечатки и мусор
        'каторый', 'каторая', 'каторое', 'каторые', 'который', 'которая', 'которое', 'которые',
        'кокие', 'какие', 'какой', 'какая',
    ];

    private const NOISE_WORDS = [
        'меня', 'должен', 'должна', 'должны', 'нужно', 'нужен', 'нужна', 'нужны',
        'имеется', 'имеются', 'формате', 'формат', 'количество', 'каких', 'каком',
        'какими', 'дата', 'даты', 'дате', 'дату', 'период', 'периода', 'периоде',
        'диапазон', 'список', 'списке', 'перечень', 'количеств', 'число', 'счёт', 'счет',
        'найти', 'найди', 'покажи', 'показ', 'показать', 'выведи', 'выводить', 'давай', 'дай',
        'есть', 'присутствуют', 'упоминается', 'говорится', 'про', 'от'
    ];

    public function __construct(private GroqClient $groq) {}

    public function ask(string $question, ?int $currentUserId = null): array
    {
        $question = trim($question);
        if ($question === '') return $this->emptyAnswer('Пожалуйста, задайте вопрос.');
        if (preg_match('/\[[^\]]*(?:имя|назван|компан|архив|your name|company name|placeholder)[^\]]*\]/iu', $question)) {
            return $this->emptyAnswer('Замените текст в квадратных скобках конкретным именем или названием.');
        }
        if ($this->isGreeting($question)) return $this->greetingAnswer();
        if ($this->isThanks($question)) return $this->emptyAnswer('Пожалуйста! Обращайтесь ещё.');
        if ($this->isGoodbye($question)) return $this->emptyAnswer('До свидания!');

        try {
            $plan = $this->plan($question);
            
            Log::info('Archivist Plan', [
                'question' => $question,
                'person' => $plan['person'],
                'keywords' => $plan['keywords'],
                'doc_type' => $plan['doc_type'],
                'intent' => $plan['intent']
            ]);

            [$documents, $chunks, $total] = $this->search($plan, $currentUserId);
            return $this->answer($question, $plan, $documents, $chunks, $total);
        } catch (\Throwable $e) {
            Log::error('Archivist request failed.', ['error' => $e->getMessage(), 'question' => $question]);
            return $this->emptyAnswer('Не удалось выполнить поиск. Попробуйте перефразировать запрос.');
        }
    }

    private function greetingAnswer(): array {
        return ['answer' => "Здравствуйте! Я ИИ-Архивист DocSign.\n\nМогу помочь:\n• Найти документы по дате, типу, формату\n• Показать документы для подписи\n• Найти просроченные документы или черновики\n• Подсчитать документы по любому критерию\n\nЗадайте вопрос на русском или таджикском языке.", 'documents' => [], 'meta' => ['found_count' => 0, 'intent' => 'greeting']];
    }

    private function emptyAnswer(string $text): array {
        return ['answer' => $text, 'documents' => [], 'meta' => ['found_count' => 0, 'intent' => 'info']];
    }

    private function isGreeting(string $q): bool {
        $q = mb_strtolower(preg_replace('/[^\pL\pN\s]/u', '', $q));
        return in_array($q, ['привет', 'здравствуй', 'здравствуйте', 'салом', 'hello', 'hi', 'добрый день', 'доброе утро', 'салам', 'ассалом']);
    }

    private function isThanks(string $q): bool {
        $q = mb_strtolower(preg_replace('/[^\pL\pN\s]/u', '', $q));
        return in_array($q, ['спасибо', 'благодарю', 'рахмат', 'ташаккур', 'thanks', 'thank you']);
    }

    private function isGoodbye(string $q): bool {
        $q = mb_strtolower(preg_replace('/[^\pL\pN\s]/u', '', $q));
        return in_array($q, ['пока', 'до свидания', 'досвидания', 'bye', 'goodbye', 'хаир', 'худоҳ']);
    }

    private function plan(string $question): array
    {
        $plan = $this->localParse($question);
        if (!$this->hasEnoughLocalSignals($plan)) {
            $groqResult = $this->groq->json(
                'Parse user question about document archive. Today: ' . now()->format('d.m.Y') . '. Return STRICT JSON: {"intent":"content|count","direction":"received|sent|any","sign_task":bool,"deadline_window":"today|7days|30days|overdue|null","format":"pdf|word|excel|rtf|null","doc_type":"договор|аренда|заявление|счёт|доверенность|приказ|справка|акт|тз|отпуск|больничный|командировка|автобиография|описание|null","status":"signed|rejected|pending|draft|null","date_from":"YYYY-MM-DD|null","date_to":"YYYY-MM-DD|null","date_field":"created|deadline|both","person":"name|null","personal_scope":bool,"keywords":["words"]}. personal_scope=true if user asks about THEIR documents. Do NOT include generic words in keywords.',
                $question, 0.1
            );
            if (!empty($groqResult)) $plan = $this->mergeGroqIntoPlan($plan, $groqResult);
        }
        if (!$this->questionContainsDate($question)) {
            $plan['date_from'] = null; $plan['date_to'] = null; $plan['date_field'] = 'created';
        }
        return $this->validatePlan($plan);
    }

    private function questionContainsDate(string $question): bool {
        return preg_match('/\b\d{1,2}[.\/-]\d{1,2}[.\/-]\d{2,4}\b|\b\d{4}-\d{2}-\d{2}\b|январ|феврал|март|апрел|ма[ейя]|июн|июл|август|сентябр|октябр|ноябр|декабр|сегодня|вчера|завтра|недел|месяц|году|года|имрӯз|дирӯз|пагоҳ|моҳ|сол|today|yesterday|tomorrow|this week|last week|this month|last month|this year|last year/iu', $question) === 1;
    }

    private function localParse(string $question): array
    {
        $q = mb_strtolower($question);
        $p = ['intent' => 'content', 'direction' => 'any', 'sign_task' => false, 'deadline_window' => null, 'format' => null, 'doc_type' => null, 'date_from' => null, 'date_to' => null, 'date_field' => 'created', 'status' => null, 'person' => null, 'has_qr' => false, 'personal_scope' => false, 'keywords' => [], 'question' => $question];

        $currentYear = now()->format('Y');
        $p['has_qr'] = preg_match('/\bqr\b|qr[\s-]*(?:код|code)|куар[\s-]*код|рамзи\s+qr/iu', $q) === 1;

        // 0. PERSONAL SCOPE
        if (preg_match('/\b(?:у\s+меня|мои|моих|моем|моём|моими|мой\s+(?:документ|файл)|мои\s+(?:документы|файлы)|в\s+моем\s+архиве)\b/iu', $question)
            || preg_match('/\b(?:my\s+(?:documents?|files?)|documents?\s+(?:i\s+have|i\s+own)|mine)\b/iu', $question)
            || preg_match('/\b(?:ҳуҷҷатҳои\s+ман|файлҳои\s+ман|ман)\b/iu', $question)) {
            $p['personal_scope'] = true;
        }

        $words = preg_split('/\s+/u', trim($question));
        $isShortQuery = count($words) <= 2;

        // 1. ДАТЫ
        $datePattern = '(?:\d{1,2}[.\/-]\d{1,2}[.\/-]\d{2,4}|\d{4}-\d{2}-\d{2})';
        if (preg_match('/(?<!\d)(' . $datePattern . ')\s*(?:до|по|—|–|to|through|until)\s*(' . $datePattern . ')(?!\d)/iu', $question, $m)) {
            $from = $this->normalizeDate($m[1]); $to = $this->normalizeDate($m[2]);
            if ($from && $to) { $p['date_from'] = min($from, $to); $p['date_to'] = max($from, $to); $p['date_field'] = $this->dateFieldForQuestion($q); }
        } elseif (preg_match('/(?<!\d)(' . $datePattern . ')(?!\d)/u', $question, $m)) {
            $parsed = $this->normalizeDate($m[1]);
            if ($parsed) { $p['date_from'] = $parsed; $p['date_to'] = $parsed; $p['date_field'] = $this->dateFieldForQuestion($q); }
        } elseif (preg_match('/(январ|феврал|март|апрел|ма|июн|июл|август|сентябр|октябр|ноябр|декабр)[а-яё]*\s+(\d{2,4})?/iu', $q, $m)) {
            $monthStr = $m[1]; $year = $m[2] ?? $currentYear;
            if (strlen($year) === 2) $year = ((int)$year < 50 ? '20' : '19') . $year;
            $months = ['январ' => '01', 'феврал' => '02', 'март' => '03', 'апрел' => '04', 'ма' => '05', 'июн' => '06', 'июл' => '07', 'август' => '08', 'сентябр' => '09', 'октябр' => '10', 'ноябр' => '11', 'декабр' => '12'];
            foreach ($months as $key => $num) {
                if (str_starts_with($monthStr, $key)) {
                    $p['date_from'] = $year . '-' . $num . '-01';
                    $p['date_to'] = $year . '-' . $num . '-' . date('t', strtotime($year . '-' . $num . '-01'));
                    $p['date_field'] = $this->dateFieldForQuestion($q);
                    break;
                }
            }
        }

        // 2. СТАТУС
        if (preg_match('/отклон|rejected|declined/iu', $q)) $p['status'] = 'rejected';
        elseif (preg_match('/archive|архив|подписан|completed|signed|заверш/iu', $q) && !preg_match('/не подписан/iu', $q)) $p['status'] = 'signed';
        elseif (preg_match('/черновик|черновики|\bdraft\b/iu', $q)) $p['status'] = 'draft';
        elseif (preg_match('/активн|active|на подписан|pending|ожидает/iu', $q)) $p['status'] = 'pending';

        // 3. ПОДПИСЬ
        if ($p['status'] !== 'signed' && $p['status'] !== 'rejected' && preg_match('/подписа[ть]|нужно подписать|должен подписать|жду подписи|мне подписать/iu', $q)) {
            $p['sign_task'] = true; $p['direction'] = 'received'; $p['personal_scope'] = true;
        }

        // 4. НАПРАВЛЕНИЕ
        if (!$p['sign_task']) {
            if (preg_match('/мне (отправ|присла)|получил|входящ|received/iu', $q)) { $p['direction'] = 'received'; $p['personal_scope'] = true; }
            elseif (preg_match('/я отправил|исходящ|на кого отправил|sent|отправлял/iu', $q)) { $p['direction'] = 'sent'; $p['personal_scope'] = true; }
        }

        // 5. ДЕДЛАЙНЫ
        if (preg_match('/просроч|не успел|overdue|expired/iu', $q)) $p['deadline_window'] = 'overdue';
        elseif (preg_match('/сегодня/iu', $q)) $p['deadline_window'] = 'today';
        elseif (preg_match('/7 дней|недел|скоро/iu', $q)) $p['deadline_window'] = '7days';
        elseif (preg_match('/месяц|30 дней/iu', $q)) $p['deadline_window'] = '30days';

        // 6. ФОРМАТЫ
        $formatDetected = false;
        if (preg_match('/\bpdf\b|пдф/iu', $q)) { $p['format'] = 'pdf'; $formatDetected = true; }
        elseif (preg_match('/\bword\b|ворд|\bdocx?\b/iu', $q)) { $p['format'] = 'word'; $formatDetected = true; }
        elseif (preg_match('/\bexcel\b|эксел|\bxlsx?\b/iu', $q)) { $p['format'] = 'excel'; $formatDetected = true; }
        elseif (preg_match('/\brtf\b/iu', $q)) { $p['format'] = 'rtf'; $formatDetected = true; }
        
        if ($formatDetected && $isShortQuery && !$p['personal_scope']) $p['personal_scope'] = true;

        // 7. ТИПЫ
        $isShortTypeQuery = false;
        if (preg_match('/договор|контракт|шартнома|соглашение/iu', $q)) { $p['doc_type'] = 'договор'; }
        elseif (preg_match('/заявлени|ариза/iu', $q)) { $p['doc_type'] = 'заявление'; }
        elseif (preg_match('/счёт|счет|инвоис|фактур/iu', $q)) { $p['doc_type'] = 'счёт'; }
        elseif (preg_match('/доверенност/iu', $q)) { $p['doc_type'] = 'доверенность'; }
        elseif (preg_match('/приказ|фармон|распоряжен/iu', $q)) { $p['doc_type'] = 'приказ'; }
        elseif (preg_match('/справк|маълумотнома/iu', $q)) { $p['doc_type'] = 'справка'; }
        elseif (preg_match('/техническое задание|техзадание|\bтз\b/iu', $q)) { 
            $p['doc_type'] = 'тз'; 
            $isShortTypeQuery = true; 
        }
        elseif (preg_match('/отпуск|рухсат/iu', $q)) { $p['doc_type'] = 'отпуск'; }
        elseif (preg_match('/автобиограф|тарҷумаи ҳол|биограф/iu', $q)) { $p['doc_type'] = 'автобиография'; }
        elseif (preg_match('/описани|формализованное описание|тавсиф/iu', $q)) { $p['doc_type'] = 'описание'; }

        // 🔥 ВАЖНО: Если запрос короткий и определен тип, добавляем ключевые слова типа в общий поиск
        if ($isShortTypeQuery && count($words) <= 3) {
             $typeKws = self::TYPE_KEYWORDS[$p['doc_type']] ?? [];
             foreach ($typeKws as $kw) {
                 if (mb_strlen($kw) >= 2) $p['keywords'][] = $kw;
             }
             $p['keywords'] = array_values(array_unique($p['keywords']));
        }

        // 8. СЧЁТЧИК
        if (preg_match('/скольк|сколька|сколько|количество|how\s*many|count|чанд|шумора/iu', $q)) $p['intent'] = 'count';

        // 9. ИМЯ ЧЕЛОВЕКА
        $foundPerson = null;
        if (preg_match('/(?:с|со|от|для|про|у|созданн(?:ые|ых|ый|ого)|отправл(?:енные|ял|ен|ена))\s+([а-яёa-z][а-яёa-z\s\-]{1,}(?:\s+[а-яёa-z][а-яёa-z\s\-]*)*)/iu', $question, $m)) {
            $personName = trim($m[1]);
            $filteredWords = [];
            foreach (preg_split('/\s+/u', $personName) as $word) {
                if (mb_strlen($word) >= 2 && !$this->isQueryNoise($word)) {
                    $filteredWords[] = $word;
                }
            }
            if (count($filteredWords) > 0) {
                $foundPerson = mb_strtolower(implode(' ', $filteredWords));
                $p['person'] = $foundPerson;
            }
        }

        // 10. КЛЮЧЕВЫЕ СЛОВА (Очистка от шума и имени человека)
        $keywordQuestion = preg_replace('/(?<!\d)' . $datePattern . '(?!\d)/u', ' ', $q) ?? $q;
        $rawKeywords = [];
        if (!$p['has_qr']) {
            foreach (preg_split('/\s+/u', $keywordQuestion) ?: [] as $w) {
                $w = trim(preg_replace('/[^\pL\pN\-]/u', '', $w) ?? '');
                // Игнорируем слово, если оно уже распознано как имя человека или является шумом
                if (mb_strlen($w) >= 2 && !$this->isQueryNoise($w) && $w !== $foundPerson) {
                    $rawKeywords[] = $w;
                }
            }
        }
        // Объединяем с keywords из типа документа (если были добавлены выше)
        $finalKeywords = array_values(array_unique(array_merge($p['keywords'], $rawKeywords)));
        $p['keywords'] = array_slice($finalKeywords, 0, 6);
        
        return $p;
    }

    private function isDeadlineFieldQuestion(string $question): bool { return preg_match('/дедлайн|deadline|due\s*date|срок|мӯҳлат|мухлат/iu', $question) === 1; }
    private function dateFieldForQuestion(string $question): string {
        $mentionsDeadline = $this->isDeadlineFieldQuestion($question);
        $mentionsCreation = preg_match('/создан|дата создания|created|creation|загружен|uploaded/iu', $question) === 1;
        if ($mentionsDeadline && $mentionsCreation) return 'both';
        if ($mentionsDeadline) return 'deadline';
        if ($mentionsCreation) return 'created';
        return 'both';
    }

    private function hasEnoughLocalSignals(array $p): bool {
        return $p['format'] !== null || $p['doc_type'] !== null || $p['sign_task'] || $p['deadline_window'] !== null || $p['date_from'] !== null || $p['status'] !== null || $p['person'] !== null || $p['has_qr'] || $p['intent'] === 'count' || $p['direction'] !== 'any' || $p['personal_scope'];
    }

    private function mergeGroqIntoPlan(array $p, array $g): array {
        foreach (['intent','direction','deadline_window','format','doc_type','date_from','date_to','date_field','status','person'] as $k) {
            if (empty($p[$k]) && !empty($g[$k])) $p[$k] = $g[$k];
        }
        if (!empty($g['sign_task'])) { $p['sign_task'] = true; $p['personal_scope'] = true; }
        if (!empty($g['personal_scope'])) $p['personal_scope'] = true;
        if (isset($g['keywords']) && is_array($g['keywords'])) {
            $keywords = $this->normalizeKeywords($g['keywords']);
            if ($keywords !== []) $p['keywords'] = $keywords;
        }
        return $p;
    }

    private function isQueryNoise(string $word): bool {
        $wordLower = mb_strtolower($word);
        if (in_array($wordLower, self::QUERY_STOP_WORDS, true)) return true;
        if (in_array($wordLower, self::NOISE_WORDS, true)) return true;
        foreach (array_merge(array_keys(self::FORMAT_GROUPS), array_merge(...array_values(self::FORMAT_GROUPS)), array_merge(...array_values(self::TYPE_KEYWORDS))) as $filterWord) {
            if (mb_strlen($filterWord) >= 3 && str_starts_with($wordLower, mb_strtolower($filterWord))) return true;
        }
        return preg_match('/^(?:документ\w*|файл\w*|архив\w*|счет\w*|счёт\w*|pdf|ворд\w*|эксел\w*|подпис\w*|отправ\w*|получ\w*|входящ\w*|исходящ\w*|сегодня|завтра|вчера|недел\w*|месяц\w*|день|дня|дней|число|количеств\w*|сколько|найд\w*|покаж\w*|просроч\w*|отклон\w*|заверш\w*|черновик\w*|ожида\w*|активн\w*|срок\w*|дедлайн\w*|создан\w*|creation|created|uploaded|загружен\w*|дат\w*|период\w*|диапазон\w*|рахмат|салом|ҳуҷҷат\w*|файл\w*|бойгонӣ\w*|маълумот\w*)$/u', $wordLower) === 1;
    }

    private function validatePlan(array $p): array {
        $format = $p['format'] ?? null;
        if (!is_string($format) || !array_key_exists($format, self::FORMAT_GROUPS)) $format = null;
        $docType = $p['doc_type'] ?? null;
        if (!is_string($docType) || !array_key_exists(mb_strtolower((string)$docType), self::TYPE_KEYWORDS)) $docType = null;
        else $docType = mb_strtolower($docType);

        return [
            'intent' => in_array($p['intent'] ?? '', ['metadata','content','count'], true) ? $p['intent'] : 'content',
            'direction' => in_array($p['direction'] ?? '', ['received','sent','any'], true) ? $p['direction'] : 'any',
            'sign_task' => (bool) ($p['sign_task'] ?? false),
            'deadline_window' => in_array($p['deadline_window'] ?? '', ['today','7days','30days','overdue'], true) ? $p['deadline_window'] : null,
            'format' => $format, 'doc_type' => $docType,
            'date_from' => $this->normalizeDate($p['date_from'] ?? null),
            'date_to' => $this->normalizeDate($p['date_to'] ?? null),
            'date_field' => in_array($p['date_field'] ?? '', ['created','deadline','both'], true) ? $p['date_field'] : 'created',
            'status' => in_array($p['status'] ?? '', ['signed','rejected','pending','draft'], true) ? $p['status'] : null,
            'person' => is_string($p['person'] ?? null) && mb_strlen(trim($p['person'])) >= 2 ? trim($p['person']) : null,
            'has_qr' => (bool) ($p['has_qr'] ?? false),
            'personal_scope' => (bool) ($p['personal_scope'] ?? false),
            'keywords' => $this->normalizeKeywords($p['keywords'] ?? []),
            'question' => is_string($p['question'] ?? null) ? $p['question'] : '',
        ];
    }

    private function normalizeDate(mixed $value): ?string {
        if (!is_string($value) || trim($value) === '' || $value === 'null') return null;
        $value = trim($value);
        if (preg_match('/^(\d{1,2})[.\/-](\d{1,2})[.\/-](\d{2,4})$/', $value, $m)) {
            $year = (int) $m[3]; if (strlen($m[3]) === 2) $year = $year < 50 ? 2000 + $year : 1900 + $year;
            return checkdate((int)$m[2], (int)$m[1], $year) ? sprintf('%04d-%02d-%02d', $year, (int)$m[2], (int)$m[1]) : null;
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
            return checkdate((int)$m[2], (int)$m[3], (int)$m[1]) ? sprintf('%04d-%02d-%02d', (int)$m[1], (int)$m[2], (int)$m[3]) : null;
        }
        return null;
    }

    private function normalizeKeywords(mixed $raw): array {
        if (!is_array($raw)) return [];
        $words = [];
        foreach ($raw as $word) {
            if (!is_string($word)) continue;
            if (preg_match('/^\d{1,4}(?:[.\/-]\d{1,2}){1,2}$/', trim($word))) continue;
            foreach (preg_split('/\s+/u', mb_strtolower(trim($word))) ?: [] as $part) {
                $part = trim(preg_replace('/[^\pL\pN\-]/u', '', $part) ?? '');
                if (mb_strlen($part) >= 2 && !$this->isQueryNoise($part)) {
                    if (preg_match('/\p{Cyrillic}/u', $part)) $part = preg_replace('/(?:иями|ями|ами|ого|ему|ому|ыми|ими|ее|ая|яя|ое|ые|ие|ых|их|ым|им|ом|ем|ую|юю|ой|ей|а|я|ы|и|е|у|ю|ь|ся|сь)$/u', '', $part) ?? $part;
                    if (mb_strlen($part) >= 2 && !$this->isQueryNoise($part)) $words[] = $part;
                }
            }
        }
        return array_values(array_unique(array_slice($words, 0, 8)));
    }

    private function keywordPatterns(string $keyword): array {
        if (!preg_match('/^\d{4,}$/', $keyword)) return ['%' . $keyword . '%'];
        $groups = []; $remaining = $keyword;
        while (strlen($remaining) > 3) { array_unshift($groups, substr($remaining, -3)); $remaining = substr($remaining, 0, -3); }
        array_unshift($groups, $remaining);
        $patterns = ['%' . $keyword . '%'];
        foreach ([' ', ',', '.'] as $separator) $patterns[] = '%' . implode($separator, $groups) . '%';
        return array_values(array_unique($patterns));
    }

    private function search(array $plan, ?int $currentUserId): array
    {
        $query = Document::query()->whereNull('deleted_at');
        $today = now()->startOfDay();

        // 1. ПРИНАДЛЕЖНОСТЬ
        if ($currentUserId) {
            if ($plan['personal_scope'] || $plan['direction'] !== 'any' || $plan['sign_task']) {
                $query->where(function ($q) use ($currentUserId, $plan) {
                    if ($plan['sign_task'] || $plan['direction'] === 'received') {
                        $q->where('receiver_id', $currentUserId);
                    } elseif ($plan['direction'] === 'sent') {
                        $q->where('created_by', $currentUserId);
                    } else {
                        $q->where('created_by', $currentUserId)
                          ->orWhere('sender_id', $currentUserId)
                          ->orWhere('receiver_id', $currentUserId);
                    }
                });
            } else {
                $query->visibleToAuth();
            }
        } else {
            $query->visibleToAuth();
        }

        // 2. ДЕДЛАЙНЫ
        switch ($plan['deadline_window']) {
            case 'today': $query->whereDate('deadline', $today)->whereNotIn('status', self::FINISHED_STATUSES); break;
            case '7days': $query->whereBetween('deadline', [$today, $today->copy()->addDays(7)->endOfDay()])->whereNotIn('status', self::FINISHED_STATUSES); break;
            case '30days': $query->whereBetween('deadline', [$today, $today->copy()->addDays(30)->endOfDay()]); break;
            case 'overdue': $query->where('deadline', '<', $today)->whereNotIn('status', self::FINISHED_STATUSES); break;
        }

        // 3. СТАТУС
        if ($plan['status']) {
            $statusMap = ['signed' => ['signed', 'completed', 'approved', 'archive'], 'rejected' => ['rejected', 'declined', 'failed'], 'pending' => ['pending', 'active', 'processing'], 'draft' => ['draft']];
            $statuses = $statusMap[$plan['status']] ?? [];
            if ($statuses !== []) $query->whereIn('status', $statuses);
        }

        if ($plan['sign_task'] || ($plan['deadline_window'] !== null && $plan['status'] !== 'signed') || $plan['status'] === 'pending') {
            $query->whereNotIn('status', self::SIGNED_STATUSES);
        }

        if ($plan['has_qr']) {
            $query->whereHas('signatures', fn($q) => $q->whereNotNull('signature')->where('signature', '!=', ''));
        }

        // 4. ФОРМАТ
        if ($plan['format']) {
            $exts = self::FORMAT_GROUPS[$plan['format']] ?? [$plan['format']];
            $query->where(function ($q) use ($exts) {
                foreach ($exts as $ext) {
                    $lowerExt = strtolower($ext);
                    $q->orWhereRaw('LOWER(file_path) LIKE ?', ['%.' . $lowerExt])
                      ->orWhereRaw('LOWER(type) LIKE ?', ['%.' . $lowerExt])
                      ->orWhereRaw('LOWER(type) = ?', [$lowerExt]);
                }
            });
        }

        // 5. ТИП ДОКУМЕНТА (Расширенный поиск: заголовок + тип + содержимое)
        if ($plan['doc_type']) {
            $typeKeywords = self::TYPE_KEYWORDS[$plan['doc_type']] ?? [$plan['doc_type']];
            $query->where(function ($q) use ($typeKeywords) {
                foreach ($typeKeywords as $kw) {
                    $q->orWhere('title', 'like', '%' . $kw . '%')
                      ->orWhere('type', 'like', '%' . $kw . '%')
                      ->orWhereHas('index', fn($iq) => $iq->where('extracted_text', 'like', '%' . $kw . '%'))
                      ->orWhereHas('chunks', fn($cq) => $cq->where('content', 'like', '%' . $kw . '%'));
                }
            });
        }

        // 6. ДАТЫ
        $dateFields = match ($plan['date_field']) { 'deadline' => ['deadline'], 'both' => ['created_at', 'deadline'], default => ['created_at'] };
        foreach ($dateFields as $dateField) {
            if ($plan['date_from']) $query->whereDate($dateField, '>=', $plan['date_from']);
            if ($plan['date_to']) $query->whereDate($dateField, '<=', $plan['date_to']);
        }

        // 7. ЧЕЛОВЕК
        if ($plan['person']) {
            $person = $plan['person'];
            $query->where(function ($q) use ($person) {
                $q->where('title', 'like', "%{$person}%")
                  ->orWhereHas('createdBy', function ($uq) use ($person) {
                      $uq->where('name', 'like', "%{$person}%")
                         ->orWhere('email', 'like', "%{$person}%")
                         ->orWhere('phone', 'like', "%{$person}%");
                  })
                  ->orWhereHas('receiver', function ($uq) use ($person) {
                      $uq->where('name', 'like', "%{$person}%")
                         ->orWhere('email', 'like', "%{$person}%")
                         ->orWhere('phone', 'like', "%{$person}%");
                  });
            });
        }

        // 8. КЛЮЧЕВЫЕ СЛОВА
        if ($plan['keywords'] !== []) {
            $keywords = $plan['keywords'];
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    foreach ($this->keywordPatterns($keyword) as $pattern) {
                        $q->orWhere('title', 'like', $pattern)
                          ->orWhere('number', 'like', $pattern)
                          ->orWhere('type', 'like', $pattern)
                          ->orWhereHas('index', fn($iq) => $iq->where('extracted_text', 'like', $pattern)->orWhere('ai_summary', 'like', $pattern))
                          ->orWhereHas('chunks', fn($cq) => $cq->where('content', 'like', $pattern));
                    }
                }
            });
        }

        $total = (clone $query)->count();
        $documentsQuery = (clone $query)->with(['createdBy', 'receiver', 'index']);
        if ($plan['keywords'] === []) $documentsQuery->orderBy('deadline', 'desc')->orderBy('created_at', 'desc');
        
        $documents = $documentsQuery->limit(min($total, 30))->get();
        $chunks = collect();

        if ($documents->isNotEmpty()) {
            $docIds = $documents->pluck('id');
            $chunkQuery = DocumentChunk::whereIn('document_id', $docIds);
            if ($plan['keywords'] !== []) {
                $chunkQuery->where(function ($q) use ($plan) {
                    foreach ($plan['keywords'] as $keyword) {
                        foreach ($this->keywordPatterns($keyword) as $pattern) $q->orWhere('content', 'like', $pattern);
                    }
                });
            }
            $chunks = $chunkQuery->orderBy('chunk_index')->limit(90)->get();
        }

        return [$documents, $chunks, $total];
    }

    private function buildDocContext(Document $d, Collection $chunks): string {
        $lines = [];
        $ext = strtolower(pathinfo((string) $d->file_path, PATHINFO_EXTENSION));
        $lines[] = '=== DOC #' . $d->id . ' ===';
        $lines[] = 'Title: ' . ($d->title ?? '-') . ' | Type: ' . ($d->type ?? '-') . ' | Format: ' . ($ext !== '' ? strtoupper($ext) : 'unknown');
        $lines[] = 'Status: ' . $d->status;
        if ($d->deadline) {
            $days = $this->daysLeft($d->deadline);
            if ($days < 0) $lines[] = 'Deadline: ' . $d->deadline->format('d.m.Y') . ' (OVERDUE ' . abs($days) . ' days)';
            elseif ($days === 0) $lines[] = 'Deadline: ' . $d->deadline->format('d.m.Y') . ' (TODAY!)';
            else $lines[] = 'Deadline: ' . $d->deadline->format('d.m.Y') . ' (' . $days . ' days left)';
        }
        $lines[] = 'Created: ' . optional($d->created_at)->format('d.m.Y');
        $lines[] = 'Owner: ' . (optional($d->createdBy)->name ?? '?') . ' → Receiver: ' . (optional($d->receiver)->name ?? '?');
        try { if ($d->index && !empty($d->index->ai_summary)) $lines[] = 'Summary: ' . mb_substr($d->index->ai_summary, 0, 500); } catch (\Throwable $e) {}
        $docChunks = $chunks->where('document_id', $d->id);
        if ($docChunks->isNotEmpty()) $lines[] = "File content:\n" . mb_substr($docChunks->pluck('content')->implode("\n---\n"), 0, 2500);
        return implode("\n", $lines);
    }

    private function daysLeft($deadline): int {
        try { return (int) round((strtotime($deadline->format('Y-m-d')) - strtotime(now()->format('Y-m-d'))) / 86400); } catch (\Throwable $e) { return 0; }
    }

    private function answer(string $question, array $plan, Collection $documents, Collection $chunks, int $total): array {
        if ($total === 0) return ['answer' => $this->buildNoResultAnswer($plan), 'documents' => [], 'meta' => ['found_count' => 0, 'intent' => $plan['intent']]];

        $shownDocuments = $documents->take(15);
        $contextParts = []; $contextLength = 0;
        foreach ($shownDocuments as $document) {
            $docContext = mb_substr($this->buildDocContext($document, $chunks), 0, 1200);
            if ($contextLength + mb_strlen($docContext) > 10000) break;
            $contextParts[] = $docContext; $contextLength += mb_strlen($docContext);
        }
        $shownDocuments = $shownDocuments->take(count($contextParts));
        $context = implode("\n\n", $contextParts);

        $isLocalFilterQuery = $plan['has_qr'] || $plan['date_from'] !== null || $plan['date_to'] !== null || $plan['deadline_window'] !== null || $plan['personal_scope'];
        
        $result = $plan['intent'] === 'count' || $isLocalFilterQuery ? [] : $this->groq->json(
            'Ты — ИИ-Архивист DocSign. Отвечай на языке вопроса. Используй только факты из контекста. Не перечисляй номера документов. Кратко ответь по сути. Верни СТРОГО JSON: {"answer":"текст"}.',
            "ВОПРОС: {$question}\nТИП: {$plan['intent']}\nНАЙДЕНО: {$total}\n\nКОНТЕКСТ:\n{$context}", 0.2
        );

        $answerText = is_string($result['answer'] ?? null) ? $result['answer'] : '';
        $answerLower = mb_strtolower($answerText);
        $isNegative = $answerText !== '' && preg_match('/не найдено|не наш|ничего|отсутству|в контексте нет|нет информации|no documents|nothing found/iu', $answerLower);

        $cards = $shownDocuments->map(function (Document $d) use ($chunks) {
            $quote = $chunks->where('document_id', $d->id)->first()?->content;
            if ($quote && mb_strlen($quote) > 120) $quote = mb_substr($quote, 0, 120) . '...';
            return [
                'id' => $d->id, 'title' => (string) ($d->title ?? 'Документ'), 'status' => (string) ($d->status ?? '-'),
                'type' => (string) ($d->type ?? '-'), 'number' => (string) ($d->number ?? '-'), 'url' => url('/documents/' . $d->id),
                'quote' => $quote, 'deadline' => $d->deadline?->format('d.m.Y'),
                'format' => strtolower(pathinfo((string) $d->file_path, PATHINFO_EXTENSION) ?: '-'),
                'days_left' => $d->deadline ? $this->daysLeft($d->deadline) : null,
                'signed' => in_array($d->status, self::SIGNED_STATUSES, true),
                'signature_state' => in_array($d->status, self::REJECTED_STATUSES, true) ? 'rejected' : ($d->status === 'draft' ? 'draft' : (in_array($d->status, self::SIGNED_STATUSES, true) ? 'signed' : 'waiting')),
                'owner' => optional($d->createdBy)->name, 'receiver' => optional($d->receiver)->name,
            ];
        })->values();

        $finalAnswer = $plan['intent'] === 'count' || $isLocalFilterQuery || $answerText === '' || $isNegative
            ? $this->buildLocalAnswer($plan, $total)
            : $answerText;

        return ['answer' => $finalAnswer, 'documents' => $cards, 'meta' => ['found_count' => $total, 'intent' => $plan['intent']]];
    }

    private function buildLocalAnswer(array $plan, int $total): string {
        $shown = min($total, 15);
        if ($plan['intent'] === 'count') {
            $s = '';
            if ($plan['format']) $s = ' в формате ' . strtoupper($plan['format']);
            elseif ($plan['doc_type']) $s = ' типа «' . $plan['doc_type'] . '»';
            elseif ($plan['status'] === 'draft') $s = ' со статусом "черновик"';
            return ($plan['personal_scope'] ? "У вас" : "Всего найдено") . " {$total} " . $this->documentNoun($total) . $s . '.';
        }
        if ($plan['status'] === 'draft') return "Найдено {$total} " . $this->documentNoun($total) . " со статусом \"черновик\". Показаны первые {$shown}.";
        if ($plan['sign_task']) return "Найдено {$total} документов, ожидающих вашей подписи.";
        if ($plan['deadline_window'] === 'overdue') return "Найдено {$total} просроченных документов. Требуют внимания.";
        if ($plan['deadline_window'] === 'today') return "Найдено {$total} документов со сроком сегодня.";
        if ($plan['deadline_window'] === '7days') return "Найдено {$total} документов со сроком в ближайшие 7 дней.";
        if ($plan['format']) return "Найдено {$total} документов в формате " . strtoupper($plan['format']) . ". Показаны первые {$shown}.";
        if ($plan['doc_type']) return "Найдено {$total} документов типа «{$plan['doc_type']}». Показаны первые {$shown}.";
        if ($plan['person']) return "Найдено {$total} документов, связанных с «{$plan['person']}».";
        if ($plan['personal_scope']) return "У вас {$total} " . $this->documentNoun($total) . ". Показаны первые {$shown}.";
        return "Найдено {$total} документов по вашему запросу. Показаны первые {$shown}.";
    }

    private function documentNoun(int $count): string {
        $lastTwoDigits = $count % 100;
        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 14) return 'документов';
        return match ($count % 10) { 1 => 'документ', 2, 3, 4 => 'документа', default => 'документов' };
    }

    private function buildNoResultAnswer(array $plan): string {
        $parts = [];
        if ($plan['status'] === 'draft') $parts[] = 'Черновики';
        elseif ($plan['format']) $parts[] = 'Документы в формате ' . strtoupper($plan['format']);
        elseif ($plan['doc_type']) $parts[] = "Документы типа «{$plan['doc_type']}»";
        elseif ($plan['sign_task']) $parts[] = 'Документы для вашей подписи';
        elseif ($plan['personal_scope']) $parts[] = 'Ваши документы';
        else $parts[] = 'Документы по вашему запросу';

        if ($plan['date_from'] || $plan['date_to']) {
            $from = $plan['date_from'] ? date('d.m.Y', strtotime($plan['date_from'])) : null;
            $to = $plan['date_to'] ? date('d.m.Y', strtotime($plan['date_to'])) : null;
            $period = ($from && $to && $from !== $to) ? "с {$from} по {$to}" : ($from ? "с {$from}" : "по {$to}");
            $parts[] = "за период {$period}";
        }

        return 'К сожалению, ' . implode(' ', $parts) . " не найдены.\n\nЧто можно попробовать:\n• Уточнить дату, имя или тип документа\n• Проверить другой формат (PDF, Word, Excel)\n• Спросить шире: «покажи все документы»";
    }
}