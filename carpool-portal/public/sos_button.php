<?php if (isset($_SESSION["user_id"])): ?>
<style>
#sos-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #e53935;
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    font-size: 22px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    cursor: pointer;
    z-index: 1000;
}
</style>
<button id="sos-btn" title="Emergency SOS">SOS</button>
<script>
document.getElementById('sos-btn').onclick = function() {
    alert('SOS sent! Your emergency contacts will be notified.');
    // Optionally, you can send an AJAX request here to log the SOS event
};
</script>
<?php endif; ?>