
//speles pamats
window.App = {
   money: 0,
    fields: 
    {
        1: { type: "Seeded", crop: "Carrot", plantedAt: Date.now(), growthTime: 5000 }, // 5 sek
        2: { type: "Empty" },
        3: { type: "Seeded", crop: "Tomato", plantedAt: Date.now(), growthTime: 10000 } // 10 sek
    }
};
window.GameTime = {
    day: 1
};
// Funkcija sēšanai
function plantField(fieldId, crop, growthTime) 
{
    window.App.fields[fieldId] = 
    {
        type: "Seeded",
        crop: crop,
        plantedAt: Date.now(),
        growthTime: growthTime || 60000//1 min
    };
    updateDisplay();
}

// Funkcija ražas novākšanai
function harvestCrop(fieldId) 
{
    if(window.App.fields[fieldId].type === "Ready")
    {
        window.App.fields[fieldId].type = "Empty";
        updateDisplay();

    }
}

function growAllFields()
{
    Object.values(window.App.fields).forEach(f => {
       if(f.type === "Seeded")
       {
        f.type = "Ready";

       }
    });
}
window.nextDay = function ()
{
    GameTime.day++;
    GameTime.energy = GameTime.maxEnergy;

    growAllFields();
    updateGameUI();

    NotificationSystem.show
    (
        `Sākusies ${GameTime.day}. diena`,
        "info"
    );
};
function updateGameUI()
{
    const dayEl = document.getElementById("dayCounter");
    const energyEl = document.getElementById("energyCounter");
    const harvestEl = document.getElementById("harvestCounter");

    if (dayEl) dayEl.textContent = `Day: ${GameTime.day}`;
    if (energyEl) energyEl.textContent = `Energy: ${GameTime.energy}`;

    if(harvestEl)
    {
       const harvestCount = Object.values(window.App.fields)
       .filter(f => f.type === "Ready").length;
       harvestEl.textContent = harvestCount;

    }
}

document.addEventListener("DOMContentLoaded", updateGameUI);


