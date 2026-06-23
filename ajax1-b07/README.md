[![Review Assignment Due Date](https://classroom.github.com/assets/deadline-readme-button-22041afd0340ce965d47ae6ef1cefeee28c7c493a6346c4f15d667ab976d596c.svg)](https://classroom.github.com/a/6czs14uL)
# assignment-8 ajax

繳交期限： 5/27

## 說明
1. 撰寫一個投票系統，可以新增投票活動、每個活動可以新增投票項目，每個人可以在每個活動中投1次票。
2. 使用 ajax 的方式與後端互動
3. 使用者不須登入，但同一個使用者名稱不可以重複在同一個投票活動中投票。
4. 投票結果請使用 chart.js 技術顯示結果，須包含直條圖及圓餅圖。
5. 投票項目除了名稱、說明外，還需要可以上傳圖片。
6. 一個投票活動的資料包含活動名稱、設置此投票活動的建置者及簡單說明。
7. 資料表請參考 schema.sql
8. 程式碼愈簡潔愈好。
9. 展示前自行新增足夠展示成果的資料。

## 使用方式
1. 開啟瀏覽器並進入 `http://localhost/ajax1-b07/index.php`
2. 直接新增投票活動、投票項目與票選項目
3. 現成示範資料已於第一次載入時建立：最愛水果投票、最喜歡的程式語言
4. 投票結果會同時顯示直條圖與圓餅圖

## 檔案
- `index.php`：前端頁面與 AJAX 邏輯
- `api.php`：後端 API，負責資料庫操作
- `schema.sql`：資料表設計
- `style.css`：頁面樣式
- `uploads/`：上傳圖片儲存位置
   
