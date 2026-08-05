import base64
import sqlite3

import cv2
import face_recognition
import numpy as np
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel

app = FastAPI(title="DocSign Face ID Service")

DB = "faces.db"
THRESHOLD = 0.6  # меньше = строже


def get_db():
    conn = sqlite3.connect(DB)
    conn.execute("""
        CREATE TABLE IF NOT EXISTS faces (
            user_id INTEGER PRIMARY KEY,
            embedding TEXT NOT NULL
        )
    """)
    return conn


class FaceIn(BaseModel):
    user_id: int
    image: str  # base64 (data:image/jpeg;base64,...)


def decode_image(b64: str):
    if "," in b64:
        b64 = b64.split(",", 1)[1]
    data = base64.b64decode(b64)
    arr = np.frombuffer(data, dtype=np.uint8)
    img = cv2.imdecode(arr, cv2.IMREAD_COLOR)
    if img is None:
        raise HTTPException(400, "Плохое изображение")
    return img


def get_embedding(img):
    rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
    boxes = face_recognition.face_locations(rgb)
    if not boxes:
        raise HTTPException(404, "Лицо не найдено в кадре")
    # берём самое большое лицо в кадре
    top, right, bottom, left = max(
        boxes, key=lambda b: (b[2] - b[0]) * (b[1] - b[3])
    )
    return face_recognition.face_encodings(rgb, [(top, right, bottom, left)])[0]


@app.get("/health")
def health():
    return {"ok": True}


@app.post("/register")
def register(data: FaceIn):
    enc = get_embedding(decode_image(data.image))
    conn = get_db()
    conn.execute(
        "INSERT OR REPLACE INTO faces (user_id, embedding) VALUES (?, ?)",
        (data.user_id, ",".join(map(str, enc))),
    )
    conn.commit()
    conn.close()
    return {"ok": True}


@app.post("/verify")
def verify(data: FaceIn):
    enc = get_embedding(decode_image(data.image))
    conn = get_db()
    row = conn.execute(
        "SELECT embedding FROM faces WHERE user_id = ?", (data.user_id,)
    ).fetchone()
    conn.close()

    if not row:
        return {"match": False, "registered": False}

    stored = np.array(list(map(float, row[0].split(","))))
    dist = float(np.linalg.norm(stored - enc))
    return {"match": bool(dist < THRESHOLD), "registered": True, "distance": dist}