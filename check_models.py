import os
import requests
import json

api_key = os.getenv("GROQ_API_KEY")

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
    print("--- Все модели ---")
    for model in models.get('data', []):
        print(f"  • {model['id']}")
    
    print("\n--- Llama модели ---")
    for model in models.get('data', []):
        if 'llama' in model['id'].lower():
            print(f"  ? {model['id']}")
