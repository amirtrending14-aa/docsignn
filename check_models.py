import os
import requests
import json
from dotenv import load_dotenv

# Автоматически находим .env файл в корне проекта (на уровень выше папки python)
# Это работает независимо от того, откуда вы запускаете скрипт
base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
env_path = os.path.join(base_dir, '.env')

# Загружаем переменные из .env в окружение
load_dotenv(dotenv_path=env_path)

# Получаем ключ из переменных окружения
api_key = os.getenv("GROQ_API_KEY")

if not api_key:
    print("❌ Ошибка: Переменная GROQ_API_KEY не найдена в .env файле!")
    print(f"Искал файл здесь: {env_path}")
    exit(1)

print(f"✅ Ключ успешно загружен из .env (начинается на {api_key[:5]}...)")

# Остальной код без изменений
response = requests.get(
    "https://api.groq.com/openai/v1/models",
    headers={
        "Authorization": f"Bearer {api_key}",
        "Content-Type": "application/json"
    }
)

if response.status_code != 200:
    print(f"Ошибка: {response.status_code}")
    print(response.text)
else:
    models = response.json()
    print("=== ДОСТУПНЫЕ МОДЕЛИ GROQ ===\n")
    for model in models.get('data', []):
        print(f"  • {model['id']}")