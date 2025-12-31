let icon = {
    success:
    '<i class="fa-solid fa-check"></i>',
    danger:
    '<i class="fa-solid fa-x"></i>',
    warning:
    '<i class="fa-solid fa-exclamation"></i>',
    info:
    '<i class="fa-solid fa-exclamation"></i>',
};

const showToast = (
    message = "Sample Message",
    toastType = "info",
    duration = 5000) => {
    if (
        !Object.keys(icon).includes(toastType))
        toastType = "info";

    let box = document.createElement("div");
    box.classList.add(
        "toast", `toast-${toastType}`);
    box.innerHTML = ` <div class="toast-content-wrapper">
                      <div class="toast-icon">
                      ${icon[toastType]}
                      </div>
                      <div class="toast-message">${message}</div>
                      <div class="toast-progress"></div>
                      </div>`;
    duration = duration || 5000;
    box.querySelector(".toast-progress").style.animationDuration =
            `${duration / 1000}s`;

    let toastAlready = 
        document.body.querySelector(".toast");
    if (toastAlready) {
        toastAlready.remove();
    }

    document.body.appendChild(box)};

// Keep all your existing code above...

// document.addEventListener('DOMContentLoaded', function() {
    let submit = document.querySelector(".custom-toast.success-toast");
    let information = document.querySelector(".custom-toast.info-toast");
    let failed = document.querySelector(".custom-toast.danger-toast");
    let warn = document.querySelector(".custom-toast.warning-toast");

    // Check if elements exist before adding event listeners
    if (submit) {
        submit.addEventListener("click", (e) => {
             console.log('test');
            e.preventDefault();
            showToast("Article Submitted Successfully", "success", 5000);
        });
    }

    if (information) {
        information.addEventListener("click", (e) => {
            console.log('test');
            e.preventDefault();
            showToast("Do POTD and Earn Coins", "info", 5000);
        });
    }

    if (failed) {
        failed.addEventListener("click", (e) => {
             console.log('test');
            e.preventDefault();
            showToast("Failed unexpected error", "danger", 5000);
        });
    }

    if (warn) {
        warn.addEventListener("click", (e) => {
             console.log('test');
            e.preventDefault();
            showToast("!warning! server error", "warning", 5000);
        });
    }
// });