
import shutil
import os

# 疑似サーバーフォルダ
SERVER_DIR = "server_files"
LOCAL_DIR = "local_files"

FILES = ["contact.csv", "idsyosai.csv", "proposal.csv"]

def download_files():
    for fname in FILES:
        src = os.path.join(SERVER_DIR, fname)
        dst = os.path.join(LOCAL_DIR, fname)
        shutil.copy(src, dst)
        print(f"ダウンロード完了: {fname}")

def upload_files():
    for fname in FILES:
        src = os.path.join(LOCAL_DIR, fname)
        dst = os.path.join(SERVER_DIR, fname)
        shutil.copy(src, dst)
        print(f"アップロード完了: {fname}")

def menu():
    while True:
        print("\n=== メニュー ===")
        print("1: CSVファイルをダウンロード")
        print("2: CSVファイルをアップロード")
        print("3: 終了")
        choice = input("番号を入力してください: ")

        if choice == "1":
            download_files()
        elif choice == "2":
            upload_files()
        elif choice == "3":
            break
        else:
            print("無効な選択です")

if __name__ == "__main__":
    menu()
