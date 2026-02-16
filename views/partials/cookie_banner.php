<?php
// Show banner only if user has not chosen yet
if (isset($_COOKIE['userChoice'])) {
    return;
}

if(isset($_COOKIE['loginID'])){
    return;
}
?>

<style>
    /* Cookie Banner Styles */
    #cookie-banner {
        position: fixed;
        left: 20px;
        right: 20px;
        bottom: 20px;
        max-width: 900px;
        margin: 0 auto;
        background: #0b1220;
        color: #e5e7eb;
        padding: 16px 18px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
        display: flex;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
        z-index: 9999;
        font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    }

    #cookie-banner p {
        margin: 0;
        font-size: 14px;
        line-height: 1.4;
    }

    .cookie-actions {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }

    .cookie-actions button {
        padding: 8px 12px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-weight: 700;
    }

    .cookie-actions .accept {
        background: #22c55e;
        color: #022c22;
    }

    .cookie-actions .reject {
        background: transparent;
        color: #e5e7eb;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    @media (max-width: 600px) {
        #cookie-banner {
            flex-direction: column;
            align-items: flex-start;
        }

        .cookie-actions {
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>

<div id="cookie-banner">
   <p>
  We use cookies to improve your experience. You can accept or reject non-essential cookies.
</p>
    <div class="cookie-actions">
        <button class="accept" id="acceptCookies">Accept</button>
        <button class="reject" id="rejectCookies">Reject</button>
    </div>
</div>

<script>
    (function() {
        const banner = document.getElementById('cookie-banner');

        function setConsent(value) {
            // set cookie for 30 days, site-wide
            const days = 30;
            const expires = new Date(Date.now() + days * 24 * 60 * 60 * 1000).toUTCString();
            document.cookie = "userChoice=" + value + "; expires=" + expires + "; path=/";
            banner.remove();
            // Optional: reload to apply storage mode immediately
            window.location.reload();
        }

        document.getElementById('acceptCookies').addEventListener('click', function() {
            setConsent('cookies');
        });

        document.getElementById('rejectCookies').addEventListener('click', function() {
            setConsent('session');
        });
    })();
</script>