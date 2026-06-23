<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>AJAX 投票系統</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="container">
    <h1>📊 線上 AJAX 投票系統</h1>
    <hr>

    <div class="main-content">
        <div class="left-panel">
            <h2>✨ 建立新投票活動</h2>
            <form id="activityForm">
                <input type="text" id="actName" placeholder="活動名稱 (必填)" required>
                <input type="text" id="actCreator" placeholder="建置者 (必填)" required>
                <textarea id="actDesc" placeholder="簡單說明..."></textarea>
                <button type="submit">發布活動</button>
            </form>

            <h2>📋 活動列表</h2>
            <div id="activityList">載入中...</div>
        </div>

        <div class="right-panel" id="interactiveZone" style="display:none;">
            <h2 id="currentActivityName">請選取活動</h2>
            <p id="currentActivityDesc"></p>
            
            <hr>
            
            <h3>➕ 新增此活動的投票項目</h3>
            <form id="candidateForm">
                <input type="hidden" id="candAuthId">
                <input type="text" id="candName" placeholder="項目名稱" required>
                <input type="text" id="candDesc" placeholder="項目說明">
                <button type="submit">新增項目</button>
            </form>

            <hr>

            <h3>🗳️ 參與投票</h3>
            <div class="voter-box">
                <input type="text" id="voterName" placeholder="請輸入您的名字 (投票者名稱)" required>
            </div>
            <div id="candidateList"></div>

            <hr>

            <h3>📈 即時投票結果</h3>
            <div class="chart-container">
                <div><canvas id="barChart"></canvas></div>
                <div><canvas id="pieChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<script>
let currentActivityId = null;
let barChartInstance = null;
let pieChartInstance = null;

document.addEventListener('DOMContentLoaded', loadActivities);

function loadActivities() {
    fetch('api.php?action=listActivities')
        .then(res => res.json())
        .then(data => {
            if(!data.success) return;
            let html = '';
            data.activities.forEach(act => {
                html += `<div class="activity-item" onclick="switchActivity(${act.id})">
                    <strong>${act.name}</strong> <small>(建立者: ${act.creator})</small>
                    <p>${act.description}</p>
                </div>`;
            });
            document.getElementById('activityList').innerHTML = html || '目前沒有任何活動。';
        });
}

function switchActivity(id) {
    currentActivityId = id;
    document.getElementById('interactiveZone').style.display = 'block';
    document.getElementById('candAuthId').value = id;

    fetch(`api.php?action=getActivity&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if(!data.success) return alert(data.message);
            document.getElementById('currentActivityName').innerText = '📌 ' + data.activity.name;
            document.getElementById('currentActivityDesc').innerText = data.activity.description;

            let html = '';
            data.activity.candidates.forEach(cand => {
                // 已移除圖片顯示結構
                html += `<div class="candidate-item">
                    <div class="cand-info">
                        <strong>${cand.name}</strong>
                        <p>${cand.description || '無描述'}</p>
                    </div>
                    <button onclick="submitVote(${cand.id})">投一票</button>
                </div>`;
            });
            document.getElementById('candidateList').innerHTML = html || '此活動尚未建立投票項目。';
            loadCharts(id);
        });
}

function submitVote(candId) {
    const voterName = document.getElementById('voterName').value.trim();
    if(!voterName) return alert('請先輸入您的名字，再進行投票！');

    const formData = new FormData();
    formData.append('activity_id', currentActivityId);
    formData.append('candidate_id', candId);
    formData.append('voter_name', voterName);

    fetch('api.php?action=vote', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            alert(data.message || (data.success ? '投票成功！' : '投票失敗'));
            if(data.success) loadCharts(currentActivityId);
        });
}

function loadCharts(id) {
    fetch(`api.php?action=getResults&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if(!data.success) return;
            const labels = data.results.map(r => r.name);
            const votes = data.results.map(r => r.votes);
            const colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

            if(barChartInstance) barChartInstance.destroy();
            if(pieChartInstance) pieChartInstance.destroy();

            barChartInstance = new Chart(document.getElementById('barChart'), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{ label: '得票數', data: votes, backgroundColor: '#36A2EB' }]
                },
                options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
            });

            pieChartInstance = new Chart(document.getElementById('pieChart'), {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{ data: votes, backgroundColor: colors.slice(0, labels.length) }]
                },
                options: { responsive: true }
            });
        });
}

document.getElementById('activityForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('name', document.getElementById('actName').value);
    formData.append('creator', document.getElementById('actCreator').value);
    formData.append('description', document.getElementById('actDesc').value);

    fetch('api.php?action=createActivity', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert('活動建立成功！');
                this.reset();
                loadActivities();
            }
        });
});

document.getElementById('candidateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('activity_id', document.getElementById('candAuthId').value);
    formData.append('name', document.getElementById('candName').value);
    formData.append('description', document.getElementById('candDesc').value);

    fetch('api.php?action=createCandidate', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert('投票項目新增成功！');
                this.reset();
                switchActivity(currentActivityId);
            } else {
                alert(data.message);
            }
        });
});
</script>
</body>
</html>