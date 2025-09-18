import csv
from openpyxl import Workbook
import sys

def convert_csv_to_excel(csv_file, excel_file):
    with open(csv_file, encoding='shift_jis') as f:
        reader = csv.reader(f)
        rows = list(reader)

    # 1行目に項目名、2行目に入力内容がある前提
    headers = rows[0]
    values = rows[1] if len(rows) > 1 else [''] * len(headers)
    pairs = list(zip(headers, values))

    wb = Workbook()
    ws = wb.active
    ws.title = "入力内容"

    # 左側（1〜15項目）
    for i in range(15):
        ws.cell(row=i+1, column=1, value=pairs[i][0])  # 項目名
        ws.cell(row=i+1, column=2, value=pairs[i][1])  # 入力内容
        ws.cell(row=i+1, column=3, value="")           # 回答欄

    # 右側（16〜30項目）
    for i in range(15, 30):
        ws.cell(row=i-14, column=5, value=pairs[i][0])  # 項目名
        ws.cell(row=i-14, column=6, value=pairs[i][1])  # 入力内容
        ws.cell(row=i-14, column=7, value="")           # 回答欄

    wb.save(excel_file)
    print(f"Excelファイル '{excel_file}' に保存しました。")

# 実行時の引数処理
if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("使い方: python convert.py input.csv output.xlsx")
    else:
        convert_csv_to_excel(sys.argv[1], sys.argv[2])