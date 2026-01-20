
let harvestedCount = 0;
function saveHarvestToServer(cellId)
{
    fetch("/modules/api.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            action: "harvest",
            cellId: cellId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            console.log(`Harvested: ${data.cellId}`);
        } else {
            console.warn(data.error);
        }
    });
}
window.harvestCrop = function(cell)
{
 const state = cell._state;
    if (!state.grown) return;

    const seed = SEEDS[state.seedId];

    cell.querySelectorAll(".seed-indicator, .seed-overlay, .cell-overlay")
        .forEach(el => el.remove());

    cell.style.backgroundImage = "url('/assets/textures/field/dirt1.png')";
    cell.dataset.type = "Empty";
    saveHarvestToServer(cell.dataset.id);
   
    window.EnergyPanel.recover(seed.harvestReward);
    harvestedCount++;

    const counterEL = document.getElementById("harvestCounter"); 
    if(counterEL) counterEL.textContent = `${harvestedCount}`;

     NotificationSystem.show(`${seed.name} removed!`, "info");

    clearTimeout(state.growTimer);
    cell._state = 
    {
        soil: "Empty",
        fertilized: false,
        seedId: null,
        growing: false,
        grown: false,
        growTimer: null
    };
}


