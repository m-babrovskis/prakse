function updateBottomPanelEnergy()
{
  const energyEl = document.getElementById("energyCounter");
  if(energyEl)
  {
      energyEl.textContent = `Energy: ${EnergyPanel.current} / ${EnergyPanel.max}`;
  }
}
//atjauino html skaititajus
function updateDisplay()
{
    //dienas panelis
    const dayEl = document.getElementById("dayCounter");
    if(dayEl) dayEl.textContent = `Day: ${window.GameTime.day}`;
    updateBottomPanelEnergy();

    const harvestEl = document.getElementById("harvestCounter");
    if(harvestEl)
    {
       const ready = Object.values(App.fields).filter(f => f.type === "Ready").length;
       harvestEl.textContent = ready;
    }
}
// Funkcija nākamajai dienai
function nextDay() 
{
    GameTime.day++;

    EnergyPanel.current = EnergyPanel.max;
    EnergyPanel.updateUI();

    updateDisplay();

    NotificationSystem.show(`Started day ${window.GameTime.day}`);
}
function syncFieldsToCells()
{
  document.querySelectorAll("#fields td").forEach(cell => {
    const id = cell.dataset.cellId;
    const field = App.fields[id];

    if(!field) return;

    if(field.type === "Ready")
    {
       cell.dataset.type = "Grown";
    }
    if(field.type === "Empty")
    {
       cell.dataset.type = "Empty";
    }
  });
}
function updateBottomPanelEnergy()
{
  const energyEl = document.getElementById("energyCounter");
  if(energyEl)
  {
    energyEl.textContent = `Energy: ${window.EnergyPanel.current} / ${window.EnergyPanel.max}`;
  }
}
document.addEventListener("DOMContentLoaded", () => 
{
   const btn = document.getElementById("nextDayBtn");
    if(btn) btn.addEventListener("click", nextDay);

    updateDisplay();
});
