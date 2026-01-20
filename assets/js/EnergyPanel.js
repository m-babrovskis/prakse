/// Globāls objekts, lai energy būtu pieejama citos failos
window.EnergyPanel = {
    current: 1000,
    max: 1000,
    
    consume: function(amount) 
    {
        this.current -= amount;
        if (this.current < 0) this.current = 0;
        this.updateUI();
    },
    
    recover: function(amount) 
    {
        this.current += amount;
        if (this.current > this.max) this.current = this.max;
        this.updateUI();
    },
    updateUI: function() 
    {
        const bar = document.getElementById("energy-bar");
        const text = document.getElementById("energy-text");
        const percent = (this.current / this.max) * 100;

        if(bar)
        {
            //mainas tikai BAR platums
           bar.style.width = percent + "%";

           if(percent < 2) bar.classList.add("blink");
           else bar.classList.remove("blink");
        }
        if(text)
        {
            text.textContent = `${this.current} / ${this.max}`;
            const percent = (this.current / this.max) * 100;
            if(percent < 2) text.classList.add("blink");
            else text.classList.remove("blink");
        }
        if(typeof updateBottomPanelEnergy === "function")
        {
           updateBottomPanelEnergy();
        }
    }
};
// Inicializē
window.EnergyPanel.updateUI();

setInterval(() => {
    window.EnergyPanel.recover(5); // atjauno 5 enerģijas vienības
}, 5000); // ik pēc 5 sekundēm





