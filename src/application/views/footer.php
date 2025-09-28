<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<div id="footerApp">
    <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>&copy; {{ year }} Financial Manager</span>
                <div class="mt-2">
                    <a href="#" class="text-primary">Privacy Policy</a> |
                    <a href="#" class="text-primary">Terms of Service</a> |
                    <a href="#" class="text-primary">Contact</a>
                </div>
            </div>
        </div>
    </footer>
</div>

<script>
createApp({
    data() {
        return {
            year: new Date().getFullYear()
        }
    }
}).mount('#footerApp');
</script>
