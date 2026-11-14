    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm pointer-events-auto"></div>

    <?php 
    $flash = getFlash();
    if ($flash): 
    ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                showToast(<?= json_encode($flash['message']) ?>, <?= json_encode($flash['type']) ?>);
            });
        </script>
    <?php endif; ?>

    <!-- System JS -->
    <script src="public/js/app.js"></script>
</body>
</html>
