import bz2
import os
import urllib.request


BASE_DIR = os.path.dirname(os.path.abspath(__file__))
APP_DIR = os.path.abspath(os.path.join(BASE_DIR, ".."))
MODELS_DIR = os.path.join(APP_DIR, "trainer", "models")


def _download(url: str, dest_path: str) -> None:
    os.makedirs(os.path.dirname(dest_path), exist_ok=True)
    tmp_path = dest_path + ".tmp"
    with urllib.request.urlopen(url) as r, open(tmp_path, "wb") as f:
        f.write(r.read())
    os.replace(tmp_path, dest_path)


def _download_and_decompress_bz2(url: str, dest_path: str) -> None:
    os.makedirs(os.path.dirname(dest_path), exist_ok=True)
    tmp_bz2 = dest_path + ".bz2.tmp"
    with urllib.request.urlopen(url) as r, open(tmp_bz2, "wb") as f:
        f.write(r.read())
    with bz2.BZ2File(tmp_bz2) as bz2f, open(dest_path + ".tmp", "wb") as out:
        out.write(bz2f.read())
    os.remove(tmp_bz2)
    os.replace(dest_path + ".tmp", dest_path)


def ensure_models() -> dict:
    """
    Ensures required dlib model files exist locally.
    Downloads them automatically if missing.
    """
    shape5_path = os.path.join(MODELS_DIR, "shape_predictor_5_face_landmarks.dat")
    resnet_path = os.path.join(MODELS_DIR, "dlib_face_recognition_resnet_model_v1.dat")

    # Official dlib model hosting (bz2).
    shape5_url = "http://dlib.net/files/shape_predictor_5_face_landmarks.dat.bz2"
    resnet_url = "http://dlib.net/files/dlib_face_recognition_resnet_model_v1.dat.bz2"

    if not os.path.exists(shape5_path):
        _download_and_decompress_bz2(shape5_url, shape5_path)
    if not os.path.exists(resnet_path):
        _download_and_decompress_bz2(resnet_url, resnet_path)

    return {"shape5": shape5_path, "resnet": resnet_path}

