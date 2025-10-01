<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>

<style>
    #footerApp {
        background: linear-gradient(135deg, #b1bcebff 0%, #cba1d1ff 100%);
        color: white;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .sticky-footer {
        padding: 2rem 0;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
        border-radius: 0 0 50px 50px;  /* Curved bottom edges to match header style; increase px for more curve */
        overflow: hidden;  /* Ensures content respects the border-radius */

    }
    .container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        max-width: 1200px;
    }
    .copyright {
        text-align: center;
        flex: 1;
    }
    .copyright span {
        font-size: 1rem;
        font-weight: 300;
        opacity: 0.9;
    }
    .links {
        display: flex;
        gap: 1.5rem;
        flex-wrap: wrap;
        justify-content: center;
       
    }
    .links a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        font-size: 0.9rem;
        transition: color 0.3s ease, transform 0.3s ease;
        position: relative;
    }
    .links a:hover {
        color: white;
        transform: translateY(-2px);
    }
    .links a::after {
        content: '';
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -4px;
        left: 50%;
        background: white;
        transition: width 0.3s ease, left 0.3s ease;
    }
    .links a:hover::after {
        width: 100%;
        left: 0;
    }
    @media (max-width: 768px) {
        .container {
            flex-direction: column;
            gap: 1rem;
        }
        .links {
            order: -1;
        }
    }
</style>

<div id="footerApp">
    <footer class="sticky-footer">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <span>&copy; {{ year }} Financial Manager</span>
                <div class="mt-2 links">
                    <a href="#" class="text-primary">Privacy Policy</a>
                    <a href="#" class="text-primary">Terms of Service</a>
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