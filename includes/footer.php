<?php
if (!isset($base_url)) $base_url = '';
?>
<script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>

<script src="<?php echo $base_url; ?>assets/js/main.js"></script>

</body>
<div class="footer">
    <p>&copy; 2025 Kinoteka. Wszelkie prawa zastrzeżone. <a href="<?php echo $base_url; ?>terms.php" class="footer-link">Regulamin</a></p>
</div>

<style>
    .footer {
        text-align: center;
        padding: 2rem 1rem;
        margin-top: 3rem;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .footer .footer-link {
        color: #0ccb4a;
        text-decoration: none;
        font-weight: 600;
    }

    .footer .footer-link:hover {
        text-decoration: underline;
    }
</style>

</html>