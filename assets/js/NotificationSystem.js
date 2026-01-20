window.NotificationSystem =
{
     container: document.getElementById("notification-container"),
    timeout: null,

    show: function(message, type="info", duration=2500)
    {
        if(!this.container) return;
        //izdzes ieprieksejo notification , ja tada ir
        this.container.innerHTML = "";

        //izveido jaunu notification
        const notif = document.createElement("div");
        notif.className = `notification ${type}`;
        notif.textContent = message;

        this.container.appendChild(notif);
        

        //animacija
        requestAnimationFrame(() => {
            notif.classList.add("show");
        });

        //automatiska pazusana
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => {
            notif.classList.remove("show");
            setTimeout(() => {
                if(notif.parentNode) notif.parentNode.removeChild(notif);

            }, 400);
        }, duration);

    }
}
