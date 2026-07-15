import asyncio
import logging
import subprocess
import re
import json
import io
from pathlib import Path
from aiogram import Bot, Dispatcher, types, F
from aiogram.filters import Command
from aiogram.fsm.context import FSMContext
from aiogram.fsm.state import State, StatesGroup
from aiogram.fsm.storage.memory import MemoryStorage
from aiogram.types import InlineKeyboardMarkup, InlineKeyboardButton, BufferedInputFile

import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt

# ============ КОНФИГУРАЦИЯ ============
BOT_TOKEN = "8227404687:AAGHcLRwZHS116RhY7TQTJmtNu2MvkCZ3To"
LARAVEL_PATH = Path("C:/OSPanel/home/docsign")
OWNER_ID = 7298046635
SUPPORT_LINK = "https://t.me/amnvamr"

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('bot.log', encoding='utf-8'),
        logging.StreamHandler()
    ]
)

bot = Bot(token=BOT_TOKEN)
storage = MemoryStorage()
dp = Dispatcher(storage=storage)

# ============ ХРАНИЛИЩЕ ЯЗЫКОВ ============
user_languages = {}

def get_user_lang(user_id: int) -> str:
    return user_languages.get(user_id, 'ru')

def set_user_lang(user_id: int, lang: str):
    user_languages[user_id] = lang

# ============ МУЛЬТИЯЗЫЧНОСТЬ ============
TRANSLATIONS = {
    'ru': {
        'welcome': '✨ <b>Добро пожаловать в DocSign!</b>\n\n🚀 Платформа электронного документооборота\n\n📋 Выберите действие:',
        'register': '🏢 Регистрация компании',
        'stats': '📊 Статистика',
        'support': '💬 Поддержка',
        'questions': '❓ Есть вопросы?',
        'lang': '🌐 Язык',
        'cancel': '❌ Отмена',
        'back': '🔙 Назад',
        'main_menu': '🏠 Главное меню',
        'company_name_prompt': '🏢 <b>Шаг 1 из 5</b>\n\nВведите название вашей компании:\n\n💡 Например: Alif Group, Tech Solutions',
        'company_name_invalid': '❌ Название должно содержать от 2 до 50 символов',
        'company_name_taken': '❌ <b>Название уже занято!</b>\n\nИспользуйте другое название.',
        'admin_name_prompt': '👤 <b>Шаг 2 из 5</b>\n\nВведите ваше имя (админа):\n\n💡 Например: Иван, Ахмад',
        'admin_name_invalid': '❌ Имя должно содержать от 2 до 50 символов',
        'email_prompt': '📧 <b>Шаг 3 из 5</b>\n\nВведите email:\n\n💡 Email будет использоваться для входа',
        'email_invalid': '❌ Неверный формат email\n💡 Пример: name@example.com',
        'email_taken': '❌ <b>Email уже зарегистрирован!</b>\n\nИспользуйте другой email.',
        'phone_prompt': '📱 <b>Шаг 4 из 5</b>\n\nВведите номер телефона:\n\n💡 Например: +992901234567',
        'phone_invalid': '❌ Введите корректный номер телефона (мин. 7 цифр)',
        'phone_taken': '❌ <b>Телефон уже зарегистрирован!</b>\n\nИспользуйте другой номер.',
        'password_prompt': '🔐 <b>Шаг 5 из 5</b>\n\nПридумайте надёжный пароль:\n\n💡 Минимум 8 символов',
        'password_short': '❌ Пароль слишком короткий\n💡 Минимум 8 символов',
        'confirm': '📋 <b>Проверьте данные:</b>\n\n🏢 Компания: <b>{company_name}</b>\n👤 Админ: <b>{admin_name}</b>\n📧 Email: <code>{email}</code>\n📱 Телефон: <code>{phone}</code>\n🔐 Пароль: {password}\n\n✅ Всё верно?',
        'create': '✅ Создать компанию',
        'success': '🎉 <b>Компания создана!</b>\n\n🏢 Название: <b>{company_name}</b>\n👤 Админ: <b>{admin_name}</b>\n📧 Email: <code>{email}</code>\n📱 Телефон: <code>{phone}</code>\n\n🔐 Сохраните данные для входа.\n\n⚠️ Удалите это сообщение!',
        'stats_title': '📊 <b>Статистика DocSign</b>\n\n',
        'stats_companies': '🏢 Компаний: <b>{total}</b>\n',
        'stats_users': '👥 Пользователей: <b>{total}</b>\n\n',
        'stats_registrations': '📅 <b>Регистрации:</b>\n• Сегодня: <b>{today}</b>\n• Неделя: <b>{week}</b>\n• Месяц: <b>{month}</b>\n\n',
        'stats_users_reg': '👤 <b>Новые пользователи:</b>\n• Сегодня: <b>{today}</b>\n• Неделя: <b>{week}</b>\n• Месяц: <b>{month}</b>',
        'stats_error': '❌ Не удалось загрузить статистику',
        'support_text': '💬 <b>Нужна помощь?</b>\n\nНапишите нам:\n👤 @amnvamr',
        'lang_select': '🌐 <b>Выберите язык:</b>',
        'lang_ru': '🇷🇺 Русский',
        'lang_tj': '🇹🇯 Тоҷикӣ',
        'lang_en': '🇬🇧 English',
        'lang_changed': '✅ Язык изменён',
        'creating_company': '⏳ Создаю компанию...',
        'error': '❌ Ошибка: {error}',
        'checking': '⏳ Проверяю...',
        'chart_company': 'Компании',
        'chart_user': 'Пользователи',
    },
    'tj': {
        'welcome': '✨ <b>Хуш омадед ба DocSign!</b>\n\n🚀 Платформаи кор бо ҳуҷҷатҳои электронӣ\n\n📋 Амалро интихоб кунед:',
        'register': '🏢 Бақайдгирии ширкат',
        'stats': '📊 Омор',
        'support': '💬 Дастгирӣ',
        'questions': '❓ Саволҳо?',
        'lang': '🌐 Забон',
        'cancel': '❌ Бекор',
        'back': '🔙 Бозгашт',
        'main_menu': '🏠 Менюи асосӣ',
        'company_name_prompt': '🏢 <b>Қадами 1 аз 5</b>\n\nНоми ширкатро ворид кунед:\n\n💡 Масалан: Alif Group, Tech Solutions',
        'company_name_invalid': '❌ Ном бояд аз 2 то 50 рамз дошта бошад',
        'company_name_taken': '❌ <b>Ном аллакай банд аст!</b>',
        'admin_name_prompt': '👤 <b>Қадами 2 аз 5</b>\n\nНоми худро ворид кунед (админ):\n\n💡 Масалан: Иван, Ахмад',
        'admin_name_invalid': '❌ Ном бояд аз 2 то 50 рамз дошта бошад',
        'email_prompt': '📧 <b>Қадами 3 аз 5</b>\n\nEmail-ро ворид кунед:\n\n💡 Email барои ворид шудан',
        'email_invalid': '❌ Формати email нодуруст\n💡 Мисол: name@example.com',
        'email_taken': '❌ <b>Email аллакай ба қайд гирифта шудааст!</b>',
        'phone_prompt': '📱 <b>Қадами 4 аз 5</b>\n\nРақами телефонро ворид кунед:\n\n💡 Масалан: +992901234567',
        'phone_invalid': '❌ Рақами телефонро дуруст ворид кунед',
        'phone_taken': '❌ <b>Телефон аллакай ба қайд гирифта шудааст!</b>',
        'password_prompt': '🔐 <b>Қадами 5 аз 5</b>\n\nПаролро ворид кунед:\n\n💡 Ҳадди ақал 8 рамз',
        'password_short': '❌ Парол кӯтоҳ аст\n💡 Ҳадди ақал 8 рамз',
        'confirm': '📋 <b>Маълумотро санҷед:</b>\n\n🏢 Ширкат: <b>{company_name}</b>\n👤 Админ: <b>{admin_name}</b>\n📧 Email: <code>{email}</code>\n📱 Телефон: <code>{phone}</code>\n🔐 Парол: {password}\n\n✅ Ҳама дуруст?',
        'create': '✅ Эҷоди ширкат',
        'success': '🎉 <b>Ширкат эҷод шуд!</b>\n\n🏢 Ном: <b>{company_name}</b>\n👤 Админ: <b>{admin_name}</b>\n📧 Email: <code>{email}</code>\n📱 Телефон: <code>{phone}</code>',
        'stats_title': '📊 <b>Омори DocSign</b>\n\n',
        'stats_companies': '🏢 Ширкатҳо: <b>{total}</b>\n',
        'stats_users': '👥 Истифодабарандагон: <b>{total}</b>\n\n',
        'stats_registrations': '📅 <b>Бақайдгирӣ:</b>\n• Имрӯз: <b>{today}</b>\n• Ҳафта: <b>{week}</b>\n• Моҳ: <b>{month}</b>\n\n',
        'stats_users_reg': '👤 <b>Истифодабарандагони нав:</b>\n• Имрӯз: <b>{today}</b>\n• Ҳафта: <b>{week}</b>\n• Моҳ: <b>{month}</b>',
        'stats_error': '❌ Оморро бор кардан нашуд',
        'support_text': '💬 <b>Кӯмак лозим?</b>\n\n👤 @amnvamr',
        'lang_select': '🌐 <b>Забонро интихоб кунед:</b>',
        'lang_ru': '🇷🇺 Русский',
        'lang_tj': '🇹🇯 Тоҷикӣ',
        'lang_en': '🇬🇧 English',
        'lang_changed': '✅ Забон тағйир ёфт',
        'creating_company': '⏳ Ширкатро эҷод мекунам...',
        'error': '❌ Хато: {error}',
        'checking': '⏳ Санҷида истодаам...',
        'chart_company': 'Ширкатҳо',
        'chart_user': 'Истифодабарандагон',
    },
    'en': {
        'welcome': '✨ <b>Welcome to DocSign!</b>\n\n🚀 Document management platform\n\n📋 Choose an action:',
        'register': '🏢 Register company',
        'stats': '📊 Statistics',
        'support': '💬 Support',
        'questions': '❓ Questions?',
        'lang': '🌐 Language',
        'cancel': '❌ Cancel',
        'back': '🔙 Back',
        'main_menu': '🏠 Main menu',
        'company_name_prompt': '🏢 <b>Step 1 of 5</b>\n\nEnter your company name:\n\n💡 Example: Alif Group, Tech Solutions',
        'company_name_invalid': '❌ Name must be 2-50 characters',
        'company_name_taken': '❌ <b>Name already taken!</b>',
        'admin_name_prompt': '👤 <b>Step 2 of 5</b>\n\nEnter your name (admin):\n\n💡 Example: John, Ahmad',
        'admin_name_invalid': '❌ Name must be 2-50 characters',
        'email_prompt': '📧 <b>Step 3 of 5</b>\n\nEnter email:\n\n💡 Email for login',
        'email_invalid': '❌ Invalid email format\n💡 Example: name@example.com',
        'email_taken': '❌ <b>Email already registered!</b>',
        'phone_prompt': '📱 <b>Step 4 of 5</b>\n\nEnter phone number:\n\n💡 Example: +992901234567',
        'phone_invalid': '❌ Enter valid phone number',
        'phone_taken': '❌ <b>Phone already registered!</b>',
        'password_prompt': '🔐 <b>Step 5 of 5</b>\n\nCreate password:\n\n💡 Minimum 8 characters',
        'password_short': '❌ Password too short\n💡 Minimum 8 characters',
        'confirm': '📋 <b>Check your data:</b>\n\n🏢 Company: <b>{company_name}</b>\n👤 Admin: <b>{admin_name}</b>\n📧 Email: <code>{email}</code>\n📱 Phone: <code>{phone}</code>\n🔐 Password: {password}\n\n✅ All correct?',
        'create': '✅ Create company',
        'success': '🎉 <b>Company created!</b>\n\n🏢 Name: <b>{company_name}</b>\n👤 Admin: <b>{admin_name}</b>\n📧 Email: <code>{email}</code>\n📱 Phone: <code>{phone}</code>',
        'stats_title': '📊 <b>DocSign Statistics</b>\n\n',
        'stats_companies': '🏢 Companies: <b>{total}</b>\n',
        'stats_users': '👥 Users: <b>{total}</b>\n\n',
        'stats_registrations': '📅 <b>Registrations:</b>\n• Today: <b>{today}</b>\n• Week: <b>{week}</b>\n• Month: <b>{month}</b>\n\n',
        'stats_users_reg': '👤 <b>New users:</b>\n• Today: <b>{today}</b>\n• Week: <b>{week}</b>\n• Month: <b>{month}</b>',
        'stats_error': '❌ Failed to load statistics',
        'support_text': '💬 <b>Need help?</b>\n\n👤 @amnvamr',
        'lang_select': '🌐 <b>Select language:</b>',
        'lang_ru': '🇷🇺 Русский',
        'lang_tj': '🇹🇯 Тоҷикӣ',
        'lang_en': '🇬🇧 English',
        'lang_changed': '✅ Language changed',
        'creating_company': '⏳ Creating company...',
        'error': '❌ Error: {error}',
        'checking': '⏳ Checking...',
        'chart_company': 'Companies',
        'chart_user': 'Users',
    }
}

def t(key, lang='ru'):
    return TRANSLATIONS.get(lang, TRANSLATIONS['ru']).get(key, key)

# ============ СОСТОЯНИЯ FSM ============
class CompanyReg(StatesGroup):
    waiting_for_company_name = State()
    waiting_for_admin_name = State()
    waiting_for_email = State()
    waiting_for_phone = State()
    waiting_for_password = State()
    confirming = State()

# ============ ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ С ЛОГИРОВАНИЕМ ============
def execute_php(script_name: str, *args) -> tuple[bool, str]:
    try:
        php_file = LARAVEL_PATH / script_name
        if not php_file.exists():
            logging.error(f"PHP file not found: {php_file}")
            return False, f"{script_name} not found"

        cmd = ['php', str(php_file)] + list(args)
        logging.info(f"Executing PHP: {' '.join(cmd)}")

        result = subprocess.run(
            cmd,
            capture_output=True,
            text=True,
            cwd=str(LARAVEL_PATH),
            timeout=30
        )

        stdout = (result.stdout or '').strip()
        stderr = (result.stderr or '').strip()

        logging.info(f"PHP stdout: {stdout}")
        logging.info(f"PHP stderr: {stderr}")
        logging.info(f"PHP return code: {result.returncode}")

        # Если есть stderr - это ошибка PHP
        if stderr:
            logging.error(f"PHP error: {stderr}")
            return False, f"PHP Error: {stderr[:200]}"

        if result.returncode != 0:
            logging.error(f"PHP returned code {result.returncode}")
            return False, f"PHP error (code {result.returncode}): {stdout[:100]}"

        if stdout.startswith("OK:"):
            return True, stdout[3:]
        elif stdout.startswith("ERROR:"):
            return False, stdout[6:]
        elif stdout.startswith("EXISTS:"):
            return True, "EXISTS"
        elif stdout.startswith("FREE:"):
            return False, "FREE"

        logging.warning(f"Unknown PHP output: {stdout[:100]}")
        return False, f"Unknown response: {stdout[:50]}"

    except subprocess.TimeoutExpired:
        logging.error("PHP script timeout")
        return False, "Timeout"
    except Exception as e:
        logging.error(f"Exception in execute_php: {str(e)}", exc_info=True)
        return False, str(e)

def check_company_name_exists(name: str) -> bool:
    success, result = execute_php('check_company_name.php', name)
    return result == "EXISTS"

def check_email_exists(email: str) -> bool:
    success, result = execute_php('check_email.php', email)
    return result == "EXISTS"

def check_phone_exists(phone: str) -> bool:
    success, result = execute_php('check_phone.php', phone)
    return result == "EXISTS"

def create_company(company_name, admin_name, email, phone, password, telegram_id) -> tuple[bool, str]:
    logging.info(f"Creating company: {company_name}, admin: {admin_name}, email: {email}")
    return execute_php('create_company.php', company_name, admin_name, email, phone, password, str(telegram_id))

def get_stats() -> dict:
    success, result = execute_php('get_stats.php')
    if success:
        try:
            return json.loads(result)
        except:
            return {}
    return {}

def is_valid_email(email):
    return bool(re.match(r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$', email))

def is_valid_phone(phone):
    digits = re.sub(r'\D', '', phone)
    return len(digits) >= 7

# ============ ГЛАВНОЕ МЕНЮ ============
async def show_main_menu(message: types.Message, state: FSMContext):
    await state.clear()
    lang = get_user_lang(message.from_user.id)

    kb = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text=t('register', lang), callback_data="register")],
        [InlineKeyboardButton(text=t('stats', lang), callback_data="stats")],
        [InlineKeyboardButton(text=t('support', lang), callback_data="support")],
        [InlineKeyboardButton(text=t('questions', lang), url=SUPPORT_LINK)],
        [InlineKeyboardButton(text=t('lang', lang), callback_data="lang")],
    ])

    await message.answer(t('welcome', lang), reply_markup=kb, parse_mode="HTML")

# ============ ХЕНДЛЕРЫ ============
@dp.message(Command("start"))
async def cmd_start(message: types.Message, state: FSMContext):
    await show_main_menu(message, state)

@dp.callback_query(F.data == "register")
async def cb_register(callback: types.CallbackQuery, state: FSMContext):
    lang = get_user_lang(callback.from_user.id)
    await state.set_state(CompanyReg.waiting_for_company_name)

    kb = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text=t('cancel', lang), callback_data="main_menu")]
    ])

    await callback.message.edit_text(t('company_name_prompt', lang), reply_markup=kb, parse_mode="HTML")
    await callback.answer()

# ШАГ 1: Название компании
@dp.message(CompanyReg.waiting_for_company_name)
async def process_company_name(message: types.Message, state: FSMContext):
    lang = get_user_lang(message.from_user.id)
    company_name = message.text.strip()

    if len(company_name) < 2 or len(company_name) > 50:
        kb = InlineKeyboardMarkup(inline_keyboard=[
            [InlineKeyboardButton(text=t('cancel', lang), callback_data="main_menu")]
        ])
        await message.answer(t('company_name_invalid', lang), reply_markup=kb, parse_mode="HTML")
        return

    checking_msg = await message.answer(t('checking', lang))
    if check_company_name_exists(company_name):
        kb = InlineKeyboardMarkup(inline_keyboard=[
            [InlineKeyboardButton(text=t('cancel', lang), callback_data="main_menu")]
        ])
        await checking_msg.edit_text(t('company_name_taken', lang), reply_markup=kb, parse_mode="HTML")
        return

    await checking_msg.delete()
    await state.update_data(company_name=company_name)
    await state.set_state(CompanyReg.waiting_for_admin_name)

    kb = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text=t('cancel', lang), callback_data="main_menu")]
    ])
    await message.answer(f"✅ <b>{company_name}</b>\n\n" + t('admin_name_prompt', lang), reply_markup=kb, parse_mode="HTML")

# ШАГ 2: Имя админа
@dp.message(CompanyReg.waiting_for_admin_name)
async def process_admin_name(message: types.Message, state: FSMContext):
    lang = get_user_lang(message.from_user.id)
    admin_name = message.text.strip()

    if len(admin_name) < 2 or len(admin_name) > 50:
        kb = InlineKeyboardMarkup(inline_keyboard=[
            [InlineKeyboardButton(text=t('cancel', lang), callback_data="main_menu")]
        ])
        await message.answer(t('admin_name_invalid', lang), reply_markup=kb, parse_mode="HTML")
        return

    await state.update_data(admin_name=admin_name)
    await state.set_state(CompanyReg.waiting_for_email)

    kb = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text=t('cancel', lang), callback_data="main_menu")]
    ])
    await message.answer(f"✅ <b>{admin_name}</b>\n\n" + t('email_prompt', lang), reply_markup=kb, parse_mode="HTML")

# ШАГ 3: Email
@dp.message(CompanyReg.waiting_for_email)
async def process_email(message: types.Message, state: FSMContext):
    lang = get_user_lang(message.from_user.id)
    email = message.text.strip().lower()

    if not is_valid_email(email):
        kb = InlineKeyboardMarkup(inline_keyboard=[
            [InlineKeyboardButton(text=t('cancel', lang), callback_data="main_menu")]
        ])
        await message.answer(t('email_invalid', lang), reply_markup=kb, parse_mode="HTML")
        return

    checking_msg = await message.answer(t('checking', lang))
    if check_email_exists(email):
        kb = InlineKeyboardMarkup(inline_keyboard=[
            [InlineKeyboardButton(text=t('cancel', lang), callback_data="main_menu")]
        ])
        await checking_msg.edit_text(t('email_taken', lang), reply_markup=kb, parse_mode="HTML")
        return

    await checking_msg.delete()
    await state.update_data(email=email)
    await state.set_state(CompanyReg.waiting_for_phone)

    kb = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text=t('cancel', lang), callback_data="main_menu")]
    ])
    await message.answer(f"✅ <code>{email}</code>\n\n" + t('phone_prompt', lang), reply_markup=kb, parse_mode="HTML")

# ШАГ 4: Телефон
@dp.message(CompanyReg.waiting_for_phone)
async def process_phone(message: types.Message, state: FSMContext):
    lang = get_user_lang(message.from_user.id)
    phone = message.text.strip()

    if not is_valid_phone(phone):
        kb = InlineKeyboardMarkup(inline_keyboard=[
            [InlineKeyboardButton(text=t('cancel', lang), callback_data="main_menu")]
        ])
        await message.answer(t('phone_invalid', lang), reply_markup=kb, parse_mode="HTML")
        return

    checking_msg = await message.answer(t('checking', lang))
    if check_phone_exists(phone):
        kb = InlineKeyboardMarkup(inline_keyboard=[
            [InlineKeyboardButton(text=t('cancel', lang), callback_data="main_menu")]
        ])
        await checking_msg.edit_text(t('phone_taken', lang), reply_markup=kb, parse_mode="HTML")
        return

    await checking_msg.delete()
    await state.update_data(phone=phone)
    await state.set_state(CompanyReg.waiting_for_password)

    kb = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text=t('cancel', lang), callback_data="main_menu")]
    ])
    await message.answer(f"✅ <code>{phone}</code>\n\n" + t('password_prompt', lang), reply_markup=kb, parse_mode="HTML")

# ШАГ 5: Пароль
@dp.message(CompanyReg.waiting_for_password)
async def process_password(message: types.Message, state: FSMContext):
    lang = get_user_lang(message.from_user.id)
    password = message.text.strip()

    if len(password) < 8:
        kb = InlineKeyboardMarkup(inline_keyboard=[
            [InlineKeyboardButton(text=t('cancel', lang), callback_data="main_menu")]
        ])
        await message.answer(t('password_short', lang), reply_markup=kb, parse_mode="HTML")
        return

    await state.update_data(password=password)
    await state.set_state(CompanyReg.confirming)

    data = await state.get_data()
    masked_pwd = '•' * len(password) + f" ({len(password)} символов)"

    kb = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text=t('create', lang), callback_data="confirm_create")],
        [InlineKeyboardButton(text=t('cancel', lang), callback_data="main_menu")],
    ])

    await message.answer(
        t('confirm', lang).format(
            company_name=data['company_name'],
            admin_name=data['admin_name'],
            email=data['email'],
            phone=data['phone'],
            password=masked_pwd
        ),
        reply_markup=kb,
        parse_mode="HTML"
    )

# ПОДТВЕРЖДЕНИЕ С ЛОГИРОВАНИЕМ
@dp.callback_query(F.data == "confirm_create")
async def cb_confirm_create(callback: types.CallbackQuery, state: FSMContext):
    lang = get_user_lang(callback.from_user.id)
    data = await state.get_data()

    logging.info(f"User {callback.from_user.id} confirming company creation")

    await callback.message.edit_text(t('creating_company', lang))

    success, result = create_company(
        data['company_name'],
        data['admin_name'],
        data['email'],
        data['phone'],
        data['password'],
        callback.from_user.id
    )

    logging.info(f"Create company result: success={success}, result={result}")

    if success:
        kb = InlineKeyboardMarkup(inline_keyboard=[
            [InlineKeyboardButton(text=t('main_menu', lang), callback_data="main_menu")]
        ])
        await callback.message.edit_text(
            t('success', lang).format(
                company_name=data['company_name'],
                admin_name=data['admin_name'],
                email=data['email'],
                phone=data['phone']
            ),
            reply_markup=kb,
            parse_mode="HTML"
        )
        logging.info(f"Company created successfully for user {callback.from_user.id}")
    else:
        kb = InlineKeyboardMarkup(inline_keyboard=[
            [InlineKeyboardButton(text=t('main_menu', lang), callback_data="main_menu")]
        ])
        await callback.message.edit_text(
            t('error', lang).format(error=result),
            reply_markup=kb,
            parse_mode="HTML"
        )
        logging.error(f"Failed to create company: {result}")

    await state.clear()
    await callback.answer()

# СТАТИСТИКА
@dp.callback_query(F.data == "stats")
async def cb_stats(callback: types.CallbackQuery):
    lang = get_user_lang(callback.from_user.id)
    loading_msg = await callback.message.edit_text("⏳")

    stats = get_stats()

    if not stats:
        kb = InlineKeyboardMarkup(inline_keyboard=[
            [InlineKeyboardButton(text=t('main_menu', lang), callback_data="main_menu")]
        ])
        await loading_msg.edit_text(t('stats_error', lang), reply_markup=kb, parse_mode="HTML")
        await callback.answer()
        return

    total_companies = stats.get('total_companies', 0)
    total_users = stats.get('total_users', 0)

    labels = [t('chart_company', lang), t('chart_user', lang)]
    sizes = [max(total_companies, 1), max(total_users, 1)]

    fig, ax = plt.subplots(figsize=(6, 6))
    ax.pie(sizes, labels=labels, autopct='%1.1f%%', startangle=90, colors=['#3498db', '#2ecc71'])
    ax.axis('equal')

    buf = io.BytesIO()
    plt.savefig(buf, format='png', bbox_inches='tight')
    plt.close(fig)
    buf.seek(0)

    text = t('stats_title', lang)
    text += t('stats_companies', lang).format(total=total_companies)
    text += t('stats_users', lang).format(total=total_users)
    text += t('stats_registrations', lang).format(
        today=stats.get('companies_today', 0),
        week=stats.get('companies_week', 0),
        month=stats.get('companies_month', 0)
    )
    text += t('stats_users_reg', lang).format(
        today=stats.get('users_today', 0),
        week=stats.get('users_week', 0),
        month=stats.get('users_month', 0)
    )

    kb = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text=t('main_menu', lang), callback_data="main_menu")]
    ])

    try:
        await loading_msg.delete()
    except:
        pass

    photo = BufferedInputFile(buf.read(), filename="stats.png")
    await callback.message.answer_photo(photo=photo, caption=text, reply_markup=kb, parse_mode="HTML")
    await callback.answer()

# ПОДДЕРЖКА
@dp.callback_query(F.data == "support")
async def cb_support(callback: types.CallbackQuery):
    lang = get_user_lang(callback.from_user.id)
    kb = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text="💬 @amnvamr", url=SUPPORT_LINK)],
        [InlineKeyboardButton(text=t('main_menu', lang), callback_data="main_menu")]
    ])
    await callback.message.edit_text(t('support_text', lang), reply_markup=kb, parse_mode="HTML")
    await callback.answer()

# ЯЗЫК
@dp.callback_query(F.data == "lang")
async def cb_lang(callback: types.CallbackQuery):
    lang = get_user_lang(callback.from_user.id)
    kb = InlineKeyboardMarkup(inline_keyboard=[
        [InlineKeyboardButton(text=t('lang_ru', lang), callback_data="lang_ru")],
        [InlineKeyboardButton(text=t('lang_tj', lang), callback_data="lang_tj")],
        [InlineKeyboardButton(text=t('lang_en', lang), callback_data="lang_en")],
        [InlineKeyboardButton(text=t('back', lang), callback_data="main_menu")]
    ])
    await callback.message.edit_text(t('lang_select', lang), reply_markup=kb, parse_mode="HTML")
    await callback.answer()

@dp.callback_query(F.data.startswith("lang_"))
async def cb_set_lang(callback: types.CallbackQuery, state: FSMContext):
    lang_code = callback.data.replace("lang_", "")
    set_user_lang(callback.from_user.id, lang_code)

    await callback.answer(t('lang_changed', lang_code))
    await state.clear()

    try:
        await callback.message.delete()
    except:
        pass

    await show_main_menu(callback.message, state)

@dp.callback_query(F.data == "main_menu")
async def cb_main_menu(callback: types.CallbackQuery, state: FSMContext):
    await state.clear()
    try:
        await callback.message.delete()
    except:
        pass
    await show_main_menu(callback.message, state)
    await callback.answer()

@dp.message(Command("cancel"))
async def cmd_cancel(message: types.Message, state: FSMContext):
    await state.clear()
    await show_main_menu(message, state)

# ============ ЗАПУСК ============
async def main():
    logging.info("🚀 Starting DocSign bot...")
    await dp.start_polling(bot)

if __name__ == "__main__":
    try:
        asyncio.run(main())
    except KeyboardInterrupt:
        logging.info("🛑 Bot stopped")