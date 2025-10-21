// Simple page chat logic using localStorage and mock reply
(() => {
  const msgsEl = document.getElementById('chatMessages');
  const input = document.getElementById('chatInput');
  const sendBtn = document.getElementById('chatSend');
  const form = document.getElementById('chatForm');
  const STORAGE_KEY = 'chat_history';

  function safeParse(v){ try { return JSON.parse(v||'[]'); } catch(e){ return []; } }
  function loadHistory(){ return safeParse(localStorage.getItem(STORAGE_KEY)); }
  function saveHistory(h){ localStorage.setItem(STORAGE_KEY, JSON.stringify(h)); }

  function render() {
    msgsEl.innerHTML = '';
    const h = loadHistory();
    h.forEach(m => {
      const el = document.createElement('div');
      el.className = 'msg ' + (m.from === 'user' ? 'user' : 'bot');
      el.textContent = m.text;
      msgsEl.appendChild(el);
    });
    msgsEl.scrollTop = msgsEl.scrollHeight;
  }

  function pushMessage(text, from='user') {
    if (!text) return;
    const h = loadHistory();
    h.push({ text, from, ts: Date.now() });
    saveHistory(h);
    render();
  }

  function mockReply(text){
    return new Promise(res => {
      setTimeout(() => {
        res({ reply: `รับข้อความ: ${text}` });
      }, 600);
    });
  }

  function submitMessage() {
    const v = input.value.trim();
    if (!v) return;
    pushMessage(v, 'user');
    input.value = '';
    mockReply(v).then(r => {
      pushMessage(r.reply || 'ขอบคุณที่ติดต่อเรา', 'bot');
    }).catch(() => {
      pushMessage('เกิดข้อผิดพลาดในการส่งข้อความ', 'bot');
    });
  }

  sendBtn.addEventListener('click', submitMessage);
  form.addEventListener('submit', e => { e.preventDefault(); submitMessage(); });
  input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      submitMessage();
    }
  });

  // initial render
  render();
})();