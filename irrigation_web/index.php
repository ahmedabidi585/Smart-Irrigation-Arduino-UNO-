<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<title>Smart Irrigation — Proteus ISIS</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: #f0f4f0;
    color: #1a1a1a;
    max-width: 480px;
    margin: 0 auto;
    min-height: 100vh;
  }

  /* Header */
  .header {
    background: #1D9E75;
    color: white;
    padding: 16px 20px 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
  }
  .header h1 { font-size: 17px; font-weight: 600; }
  .header p  { font-size: 11px; opacity: 0.85; margin-top: 2px; }
  .conn-badge {
    font-size: 11px;
    padding: 3px 10px;
    border-radius: 12px;
    background: rgba(255,255,255,0.2);
    display: flex; align-items: center; gap: 5px;
  }
  .conn-dot { width: 7px; height: 7px; border-radius: 50%; background: #aaffaa; }
  .conn-dot.off { background: #ff9999; }

  /* Tabs */
  .tabs {
    display: flex;
    background: white;
    border-bottom: 1px solid #e8ede8;
    position: sticky;
    top: 60px;
    z-index: 99;
  }
  .tab {
    flex: 1; padding: 11px 4px;
    text-align: center;
    font-size: 12px; font-weight: 500;
    color: #888;
    border-bottom: 2.5px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
    border: none; background: none;
  }
  .tab.active { color: #1D9E75; border-bottom-color: #1D9E75; }

  /* Contenu */
  .content { padding: 14px; display: none; }
  .content.active { display: block; }

  /* Cards */
  .card {
    background: white;
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 12px;
    border: 0.5px solid #e0e8e0;
  }
  .card-title {
    font-size: 11px; font-weight: 600;
    color: #888; text-transform: uppercase;
    letter-spacing: 0.5px; margin-bottom: 10px;
  }

  /* LCD */
  .lcd {
    background: #1a2e1a;
    border-radius: 8px;
    padding: 12px 16px;
    font-family: 'Courier New', monospace;
    font-size: 14px;
    color: #7af07a;
    letter-spacing: 2px;
    line-height: 1.8;
    margin-bottom: 12px;
  }

  /* Grille capteurs */
  .sensor-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  .sensor-card {
    background: white; border-radius: 10px;
    padding: 12px; border: 0.5px solid #e0e8e0;
  }
  .sensor-label { font-size: 11px; color: #888; margin-bottom: 4px; }
  .sensor-val { font-size: 22px; font-weight: 600; font-family: monospace; }
  .sensor-unit { font-size: 11px; color: #aaa; margin-left: 2px; }
  .badge {
    font-size: 10px; font-weight: 600; padding: 2px 7px;
    border-radius: 8px; float: right;
  }
  .badge.ok     { background:#e1f5ee; color:#0F6E56; }
  .badge.warn   { background:#fef3c7; color:#92400e; }
  .badge.danger { background:#fee2e2; color:#991b1b; }
  .bar-bg { height: 5px; background: #f0f0f0; border-radius: 3px; margin-top: 6px; overflow: hidden; }
  .bar-fill { height: 100%; border-radius: 3px; transition: width 0.5s; }

  /* Relais */
  .relay-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px; }
  .relay-card {
    border-radius: 10px; padding: 12px;
    border: 0.5px solid #e0e8e0;
    background: #f9fafb;
    transition: all 0.3s;
  }
  .relay-card.on { background: #e1f5ee; border-color: #1D9E75; }
  .relay-title { font-size: 12px; font-weight: 600; margin-bottom: 2px; }
  .relay-sub   { font-size: 10px; color: #888; margin-bottom: 10px; }
  .relay-led   { width: 10px; height: 10px; border-radius: 50%; display: inline-block; background: #ccc; margin-right: 5px; }
  .relay-led.on { background: #22c55e; }
  .btn-relay {
    width: 100%; padding: 8px;
    border-radius: 8px; border: 0.5px solid #ccc;
    background: white; color: #555;
    font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all 0.2s;
  }
  .btn-relay.on { background: #1D9E75; color: white; border-color: #1D9E75; }

  /* Slider potentio */
  .pot-row { margin-bottom: 14px; }
  .pot-header { display: flex; justify-content: space-between; margin-bottom: 5px; }
  .pot-label { font-size: 12px; color: #555; }
  .pot-val { font-size: 13px; font-weight: 600; font-family: monospace; }
  input[type=range] { width: 100%; height: 4px; accent-color: #1D9E75; }
  .pot-info { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px; }
  .pot-metric { background: #f0f4f0; border-radius: 7px; padding: 7px 10px; }
  .pot-metric-label { font-size: 10px; color: #888; }
  .pot-metric-val { font-size: 14px; font-weight: 600; font-family: monospace; }

  /* Boutons */
  .btn-primary {
    width: 100%; padding: 12px;
    background: #1D9E75; color: white;
    border: none; border-radius: 10px;
    font-size: 14px; font-weight: 600;
    cursor: pointer; margin-top: 10px;
    transition: background 0.2s;
  }
  .btn-primary:active { background: #0F6E56; }

  /* Seuils */
  .seuil-row { margin-bottom: 12px; }
  .seuil-header { display: flex; justify-content: space-between; margin-bottom: 4px; }
  .seuil-label { font-size: 12px; color: #555; }
  .seuil-val { font-size: 12px; font-weight: 600; font-family: monospace; color: #1D9E75; }

  /* Log */
  .log-box {
    background: #1a1a1a; border-radius: 10px;
    padding: 12px; max-height: 350px; overflow-y: auto;
  }
  .log-line { font-size: 11px; font-family: monospace; color: #88cc88; padding: 2px 0; border-bottom: 0.5px solid #2a2a2a; }
  .log-line.tx { color: #88aaff; }
  .log-line:last-child { border-bottom: none; }

  /* Graphe */
  canvas { width: 100% !important; }

  /* Status bar */
  .status-bar {
    display: flex; justify-content: space-between;
    background: #0d1f0d; padding: 5px 10px;
    border-radius: 6px; margin-bottom: 12px;
  }
  .status-bar span { font-size: 11px; font-family: monospace; color: #4adf4a; }
</style>
</head>
<body>

<!-- Header -->
<div class="header">
  <div>
    <h1>Smart Irrigation</h1>
    <p>Proteus ISIS · Arduino UNO</p>
  </div>
  <div class="conn-badge">
    <span class="conn-dot" id="conn-dot"></span>
    <span id="conn-label">En attente...</span>
  </div>
</div>

<!-- Tabs -->
<div class="tabs">
  <button class="tab active" onclick="showTab('capteurs')">Capteurs</button>
  <button class="tab" onclick="showTab('controle')">Contrôle</button>
  <button class="tab" onclick="showTab('potentio')">Potentio.</button>
  <button class="tab" onclick="showTab('journal')">Journal</button>
</div>

<!-- ═══════════════ CAPTEURS ═══════════════ -->
<div class="content active" id="tab-capteurs">

  <div class="lcd">
    <div id="lcd1">T:--.-C  H:--%</div>
    <div id="lcd2">Sol:---%  Eau:---%</div>
  </div>

  <div class="status-bar">
    <span id="last-update">Dernière MAJ : --</span>
    <span id="data-count">-- mesures</span>
  </div>

  <div class="sensor-grid">
    <div class="sensor-card">
      <div class="sensor-label">Température <span class="badge ok" id="b-temp">--</span></div>
      <div><span class="sensor-val" id="v-temp" style="color:#E24B4A">--</span><span class="sensor-unit">°C</span></div>
      <div class="bar-bg"><div class="bar-fill" id="bar-temp" style="background:#E24B4A;width:0%"></div></div>
    </div>
    <div class="sensor-card">
      <div class="sensor-label">Humidité air <span class="badge ok" id="b-hum">--</span></div>
      <div><span class="sensor-val" id="v-hum" style="color:#378ADD">--</span><span class="sensor-unit">%</span></div>
      <div class="bar-bg"><div class="bar-fill" id="bar-hum" style="background:#378ADD;width:0%"></div></div>
    </div>
    <div class="sensor-card">
      <div class="sensor-label">Humidité sol <span class="badge ok" id="b-soil">--</span></div>
      <div><span class="sensor-val" id="v-soil" style="color:#1D9E75">--</span><span class="sensor-unit">%</span></div>
      <div class="bar-bg"><div class="bar-fill" id="bar-soil" style="background:#1D9E75;width:0%"></div></div>
    </div>
    <div class="sensor-card">
      <div class="sensor-label">Niveau eau <span class="badge ok" id="b-water">--</span></div>
      <div><span class="sensor-val" id="v-water" style="color:#7F77DD">--</span><span class="sensor-unit">%</span></div>
      <div class="bar-bg"><div class="bar-fill" id="bar-water" style="background:#7F77DD;width:0%"></div></div>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Graphe Température (30 mesures)</div>
    <canvas id="chart-temp" height="100"></canvas>
  </div>
</div>

<!-- ═══════════════ CONTRÔLE ═══════════════ -->
<div class="content" id="tab-controle">

  <div class="relay-grid">
    <div class="relay-card" id="rc-rl1">
      <div class="relay-title">
        <span class="relay-led" id="led-rl1"></span>Pompe Arrosage
      </div>
      <div class="relay-sub">RL1 — Arduino pin 8</div>
      <button class="btn-relay" id="btn-rl1" onclick="toggleRelay('RL1', 1)">Éteint</button>
    </div>
    <div class="relay-card" id="rc-rl2">
      <div class="relay-title">
        <span class="relay-led" id="led-rl2"></span>Pompe Tank
      </div>
      <div class="relay-sub">RL2 — Arduino pin 9</div>
      <button class="btn-relay" id="btn-rl2" onclick="toggleRelay('RL2', 2)">Éteint</button>
    </div>
  </div>

  <div class="card">
    <div class="card-title">Seuils d'automatisation</div>
    <div class="seuil-row">
      <div class="seuil-header">
        <span class="seuil-label">Seuil humidité sol (pompe ON si en dessous)</span>
        <span class="seuil-val" id="sv-sol">40%</span>
      </div>
      <input type="range" min="10" max="80" value="40" step="1" id="sl-sol"
             oninput="document.getElementById('sv-sol').textContent=this.value+'%'">
    </div>
    <div class="seuil-row">
      <div class="seuil-header">
        <span class="seuil-label">Seuil niveau eau (pompe tank ON si en dessous)</span>
        <span class="seuil-val" id="sv-eau">30%</span>
      </div>
      <input type="range" min="10" max="80" value="30" step="1" id="sl-eau"
             oninput="document.getElementById('sv-eau').textContent=this.value+'%'">
    </div>
    <button class="btn-primary" onclick="sendSeuils()">Envoyer seuils à Arduino →</button>
    <p style="text-align:center;font-size:11px;color:#888;margin-top:8px;" id="seuil-status">Prêt</p>
  </div>
</div>

<!-- ═══════════════ POTENTIOMÈTRES ═══════════════ -->
<div class="content" id="tab-potentio">

  <div class="card">
    <div class="card-title">RV1 — Seuil humidité sol (A2)</div>
    <div class="pot-row">
      <div class="pot-header">
        <span class="pot-label">Valeur RV1</span>
        <span class="pot-val" id="rv1-pct">35%</span>
      </div>
      <input type="range" min="0" max="100" value="35" step="1" id="rv1"
             oninput="updatePot('rv1')">
      <div class="pot-info">
        <div class="pot-metric">
          <div class="pot-metric-label">Tension (V)</div>
          <div class="pot-metric-val" id="rv1-volt" style="color:#1D9E75">1.75 V</div>
        </div>
        <div class="pot-metric">
          <div class="pot-metric-label">ADC 10bit</div>
          <div class="pot-metric-val" id="rv1-adc" style="color:#7F77DD">358</div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-title">RV2 — Seuil niveau eau (A3)</div>
    <div class="pot-row">
      <div class="pot-header">
        <span class="pot-label">Valeur RV2</span>
        <span class="pot-val" id="rv2-pct">60%</span>
      </div>
      <input type="range" min="0" max="100" value="60" step="1" id="rv2"
             oninput="updatePot('rv2')">
      <div class="pot-info">
        <div class="pot-metric">
          <div class="pot-metric-label">Tension (V)</div>
          <div class="pot-metric-val" id="rv2-volt" style="color:#1D9E75">3.00 V</div>
        </div>
        <div class="pot-metric">
          <div class="pot-metric-label">ADC 10bit</div>
          <div class="pot-metric-val" id="rv2-adc" style="color:#7F77DD">614</div>
        </div>
      </div>
    </div>
  </div>

  <button class="btn-primary" onclick="sendPots()">Envoyer RV1 + RV2 à ISIS →</button>
  <p style="text-align:center;font-size:11px;color:#888;margin-top:8px;" id="pot-status">Prêt</p>
</div>

<!-- ═══════════════ JOURNAL ═══════════════ -->
<div class="content" id="tab-journal">
  <div class="card">
    <div class="card-title">Journal de communication</div>
  </div>
  <div class="log-box" id="log-box">
    <div class="log-line">En attente de données ISIS...</div>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
// ── Configuration ──────────────────────────────────────────
const API = "api.php";
const REFRESH_MS = 1000; // rafraîchissement chaque seconde

// ── État local des relais ──────────────────────────────────
const relayState = { RL1: false, RL2: false };

// ── Graphe température ────────────────────────────────────
const chartCtx = document.getElementById('chart-temp').getContext('2d');
const tempChart = new Chart(chartCtx, {
  type: 'line',
  data: {
    labels: [],
    datasets: [{
      label: 'Température °C',
      data: [],
      borderColor: '#E24B4A',
      backgroundColor: 'rgba(226,75,74,0.1)',
      tension: 0.4,
      pointRadius: 2,
      fill: true,
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { display: false },
      y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 10 } } }
    }
  }
});

// ── Onglets ────────────────────────────────────────────────
function showTab(name) {
  document.querySelectorAll('.content').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
  document.getElementById('tab-' + name).classList.add('active');
  event.target.classList.add('active');
}

// ── Mise à jour interface ──────────────────────────────────
function updateUI(d) {
  // Connexion
  document.getElementById('conn-dot').className   = 'conn-dot';
  document.getElementById('conn-label').textContent = 'ISIS connecté';

  // LCD
  document.getElementById('lcd1').textContent = `T:${parseFloat(d.temperature).toFixed(1)}C  H:${Math.round(d.humidity)}%`;
  document.getElementById('lcd2').textContent = `Sol:${Math.round(d.soil)}%  Eau:${Math.round(d.water)}%`;

  // Timestamp
  const ts = new Date(d.created_at || Date.now()).toLocaleTimeString('fr-FR');
  document.getElementById('last-update').textContent = 'Dernière MAJ : ' + ts;

  // Capteurs
  setVal('temp',  d.temperature, '%',   50,  d.status_temp);
  setVal('hum',   d.humidity,    '%',   100, d.status_hum);
  setVal('soil',  d.soil,        '%',   100, d.status_sol);
  setVal('water', d.water,       '%',   100, d.status_eau);

  // Relais
  updateRelayUI('rl1', parseInt(d.relay1) === 1);
  updateRelayUI('rl2', parseInt(d.relay2) === 1);

  addLog(`RX  T:${parseFloat(d.temperature).toFixed(1)}°C  H:${Math.round(d.humidity)}%  Sol:${Math.round(d.soil)}%  Eau:${Math.round(d.water)}%`);
}

function setVal(id, val, unit, max, status) {
  const v = parseFloat(val) || 0;
  document.getElementById('v-' + id).textContent = v.toFixed(id === 'temp' ? 1 : 0);
  document.getElementById('bar-' + id).style.width = Math.min(100, (v / max) * 100).toFixed(0) + '%';
  const b = document.getElementById('b-' + id);
  b.textContent = status || 'OK';
  b.className = 'badge ' + (status === 'OK' ? 'ok' : status === 'BAS' ? 'warn' : 'danger');
}

function updateRelayUI(id, isOn) {
  relayState[id.toUpperCase()] = isOn;
  document.getElementById('rc-' + id).className   = 'relay-card' + (isOn ? ' on' : '');
  document.getElementById('led-' + id).className  = 'relay-led'  + (isOn ? ' on' : '');
  const btn = document.getElementById('btn-' + id);
  btn.textContent = isOn ? 'Allumé' : 'Éteint';
  btn.className   = 'btn-relay' + (isOn ? ' on' : '');
}

// ── Fetch données ──────────────────────────────────────────
async function fetchData() {
  try {
    const res  = await fetch(API + '?action=latest&_=' + Date.now());
    const data = await res.json();
    if (!data.error) updateUI(data);
  } catch (e) {
    document.getElementById('conn-dot').className    = 'conn-dot off';
    document.getElementById('conn-label').textContent = 'Déconnecté';
  }
}

async function fetchHistory() {
  try {
    const res  = await fetch(API + '?action=history&_=' + Date.now());
    const rows = await res.json();
    if (!Array.isArray(rows)) return;

    const labels = rows.map(r => new Date(r.created_at).toLocaleTimeString('fr-FR'));
    const temps  = rows.map(r => parseFloat(r.temperature));

    tempChart.data.labels  = labels;
    tempChart.data.datasets[0].data = temps;
    tempChart.update('none');

    document.getElementById('data-count').textContent = rows.length + ' mesures';
  } catch(e) {}
}

// ── Commandes relais ───────────────────────────────────────
async function toggleRelay(relay, num) {
  const newState = !relayState[relay] ? 1 : 0;
  try {
    await fetch(API + '?action=command', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ command: relay, value: newState })
    });
    updateRelayUI(relay.toLowerCase(), newState === 1);
    addLog('TX  ' + relay + ':' + newState + ' → Arduino pin ' + (num === 1 ? '8' : '9'), true);
  } catch(e) { alert('Erreur envoi commande'); }
}

// ── Seuils ────────────────────────────────────────────────
async function sendSeuils() {
  const sol = document.getElementById('sl-sol').value;
  const eau = document.getElementById('sl-eau').value;
  const st  = document.getElementById('seuil-status');
  st.style.color = '#BA7517'; st.textContent = 'Envoi en cours...';
  try {
    await fetch(API + '?action=seuils', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ seuil_sol: sol, seuil_eau: eau })
    });
    st.style.color = '#1D9E75'; st.textContent = 'Seuils envoyés ✓';
    addLog('TX  SEUIL_S:' + sol + '  SEUIL_W:' + eau, true);
    setTimeout(() => { st.style.color = ''; st.textContent = 'Prêt'; }, 2000);
  } catch(e) { st.style.color = '#E24B4A'; st.textContent = 'Erreur'; }
}

// ── Potentiomètres ────────────────────────────────────────
function updatePot(id) {
  const pct  = parseInt(document.getElementById(id).value);
  const volt = (pct / 100 * 5).toFixed(2);
  const adc  = Math.round(pct / 100 * 1023);
  document.getElementById(id + '-pct').textContent  = pct + '%';
  document.getElementById(id + '-volt').textContent = volt + ' V';
  document.getElementById(id + '-adc').textContent  = adc;
}

async function sendPots() {
  const rv1 = document.getElementById('rv1').value;
  const rv2 = document.getElementById('rv2').value;
  const st  = document.getElementById('pot-status');
  st.style.color = '#BA7517'; st.textContent = 'Envoi en cours...';
  try {
    await fetch(API + '?action=command', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ command: 'RV1', value: rv1 })
    });
    await fetch(API + '?action=command', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ command: 'RV2', value: rv2 })
    });
    st.style.color = '#1D9E75'; st.textContent = 'RV1 + RV2 envoyés ✓';
    addLog('TX  RV1:' + rv1 + '%  RV2:' + rv2 + '% → ISIS', true);
    setTimeout(() => { st.style.color = ''; st.textContent = 'Prêt'; }, 2000);
  } catch(e) { st.style.color = '#E24B4A'; st.textContent = 'Erreur'; }
}

// ── Journal ────────────────────────────────────────────────
function addLog(msg, isTx = false) {
  const box  = document.getElementById('log-box');
  const ts   = new Date().toLocaleTimeString('fr-FR');
  const line = document.createElement('div');
  line.className   = 'log-line' + (isTx ? ' tx' : '');
  line.textContent = '[' + ts + '] ' + msg;
  box.insertBefore(line, box.firstChild);
  while (box.children.length > 40) box.removeChild(box.lastChild);
}

// ── Boucle principale ─────────────────────────────────────
fetchData();
fetchHistory();
setInterval(fetchData,   REFRESH_MS);
setInterval(fetchHistory, 5000);
</script>

</body>
</html>
