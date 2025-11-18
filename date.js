
function updateTime() {
    const now = new Date();
    const options = { hour: 'numeric', minute: 'numeric', hour12: true };
    document.getElementById('liveTime').textContent = now.toLocaleTimeString('en-US', options);
}
setInterval(updateTime, 1000);
updateTime();
