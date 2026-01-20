 window.SEEDS = 
{
    carrot: 
    {
        id: "carrot",
        name: "carrot",
        growTime: 10000, //augsanas ilgums ms
        energyCost: 10, //energijas teresanas
        harvestReward: 20, //energijas atgusana
        textures: 
        {
            seed: "/assets/textures/field/seeds/carrot/1_carrot.png"
        }
    }
};

function plantField(fieldId, crop) 
{   
    if (!window.App.fields[fieldId]) 
    {
        window.App.fields[fieldId] = {};
    }
    window.App.fields[fieldId].crop = crop;
    window.App.fields[fieldId].plantedAt = Date.now();
    window.App.fields[fieldId].type = "Seeded";//pievieno type
     saveFieldToServer(); // nosūta uz serveri
}
function harvestCrop(fieldId) 
{
    const field = window.App.fields[fieldId];
    if (!field || field.type !== "Ready") return;

    field.type = "Empty";
    updateDisplay();

    const seed = window.SEEDS[field.crop];
    window.EnergyPanel.recover(seed.harvestReward);
    NotificationSystem.show(`${seed.name} novākts!`, "info");
}
window.changeCellImage = changeCellImage;
window.startGrowing = startGrowing;
window.canUseEnergy = canUseEnergy;
window.canCultivate = canCultivate;
window.blockIfEmpty = blockIfEmpty;
function blockIfEmpty(cell, modeName)
{
   if(cell.dataset.type === "Empty")
   {
     NotificationSystem.show(`${modeName} Cannot be used on uncultivated land!`, "warning");
     return true;
   }
   return false;
}
function canUseEnergy(cell)
{
    if(editModeActive && cell.dataset.type === "Empty") return true;

    if(CultivatedMode && 
        (cell.dataset.type === "Plowed" || 
        cell.dataset.type.startsWith("Plowed (Fertilized)"))
    ) return true;

     if(fertilizerMode && 
        (cell.dataset.type.startsWith("Plowed") || 
        cell.dataset.type.startsWith("Cultivated"))
    ) return true;

    if(
        seedMode && 
        (
            cell.dataset.type === "Cultivated" || 
           cell.dataset.type === "Cultivated (fertilized)"
        )
       
    ) return true;

    if(cell.dataset.type === "Grown") return true;

    return false;
}
function canCultivate(cell)
{
    return(
        cell.dataset.type === "Plowed" || 
        cell.dataset.type.startsWith("Plowed (Fertilized")
    );
}
function startGrowing(cell) 
{
    const state = cell._state;
    if(!state.seedId) return;

    const seed = SEEDS[state.seedId];
    if(!seed) return;

    state.growing = true;

    //pec laika, sun air gatava novaksanai
    state.growing = setTimeout(() =>
    {
        state.growing = false;
        state.grown = true;

        const indicator = document.createElement("div"); 
        indicator.classList.add("seed-indicator");
        cell.appendChild(indicator);

        cell.dataset.type = "Grown"; 

    }, seed.growTime);
}

// funkcija, kura maina bildi šūnā
function changeCellImage(cell, type) 
{
    let fertOverlay = cell.querySelector(".cell-overlay");
    let seedOverlay = cell.querySelector(".seed-overlay");

    if (type === "Plowed") 
    {
        if (fertOverlay) fertOverlay.remove();
        if (seedOverlay) seedOverlay.remove();
        cell.style.backgroundImage = "url('/assets/textures/field/dirt_plowed1.png')";
        cell.dataset.type = "Plowed";
        return;
    } 
    else if (type === "Cultivated") 
    {
        // Atļaut Cultivated uz Plowed vai Plowed + Fertilized
        if (!cell.dataset.type.startsWith("Plowed")) return;

        cell.style.backgroundImage = "url('/assets/textures/field/dirt_cultivated1.png')";

        if (fertOverlay) 
        {
            // saglabā Fertilizer overlay
            fertOverlay.style.zIndex = "2";
            cell.dataset.type = "Cultivated (Fertilized)";
        } else 
        {
            cell.dataset.type = "Cultivated";
        }
        return;
    }  
    else if (type === "fertilized") 
    {
        // Atļaut fertilize uz Plowed vai Cultivated
        if (!cell.dataset.type.startsWith("Plowed") && !cell.dataset.type.startsWith("Cultivated")) return;
        // ja nav — izveido
        if (!fertOverlay) 
        {
          fertOverlay = document.createElement("div");
          fertOverlay.classList.add("cell-overlay");
          cell.appendChild(fertOverlay);
        }

          fertOverlay.style.backgroundImage = "url('/assets/textures/field/fertilizer.png')";
          fertOverlay.style.zIndex = "2";

         let isSeeded = cell.dataset.type.includes("(Seeded)");
          // Saglabā pamata tipu + "(Fertilized)"
        if (cell.dataset.type.startsWith("Plowed")) cell.dataset.type = "Plowed";
        else cell.dataset.type = "Cultivated";

        // Pievieno Fertilized
        cell.dataset.type += " (Fertilized)";

        // Atjauno Seeded, ja bija
        if (isSeeded) cell.dataset.type += " (Seeded)";
        return;
    } 
    else if (type === "seeded") 
    {
        // strādā tikai uz Cultivated vai Cultivated+Fertilized
        if (!cell.dataset.type.startsWith("Cultivated")) return;

        // Pamata zeme vienmēr Cultivated
        cell.style.backgroundImage = "url('/assets/textures/field/dirt_cultivated1.png')";

       // Ja Fertilizer ir, saglabā zem seedOverlay
       if (fertOverlay) fertOverlay.style.zIndex = "2";

       if (!seedOverlay) 
        {
            seedOverlay = document.createElement("div");
            seedOverlay.classList.add("seed-overlay");
            cell.appendChild(seedOverlay);
        }
        
        const seed = SEEDS["carrot"];
        seedOverlay.style.backgroundImage = `url('${seed.textures.seed}')`;

        cell._state.seedId = seed.id;
        startGrowing(cell);

        seedOverlay.style.zIndex = "4";

        if (cell.dataset.type.includes("Cultivated (Fertilized)")) 
        {
        cell.dataset.type = "Cultivated (Fertilized) (Seeded)";
        } else 
        {
        cell.dataset.type = "Cultivated (Seeded)";
        }
        startGrowing(cell);
        return;   
    }
}
/////atlasa visas šūnas
document.addEventListener("DOMContentLoaded", () => {
    window.tooltip = document.getElementById("tooltip");
const allCells = document.querySelectorAll("#fields td"); //atlasa visas sunas

allCells.forEach(cell =>  {
    cell._state = {
        soil: "Empty",
        fertilized: false,
        seedId: null,
        growing: false,
        grown: false,
        growTimer: null
    };
    const fieldId = cell.dataset.cellId;
   // Tooltip
cell.addEventListener("mouseenter", function(e)
{
    if (!window.tooltipActive) return;

   window.tooltip.textContent = `Field: ${cell.dataset.type || "Empty"}`;
   window.tooltip.classList.add("show");
});
 cell.addEventListener("mouseleave", () => {
    tooltip.classList.remove("show");
}); 
//Paint logika
cell.addEventListener("mouseenter", function() 
{
    if (!paintMode) return;

    const energyCost = 10;

    if(
        !(cell.dataset.type === "Grown") &&
        !(editModeActive && cell.dataset.type === "Empty") &&
        !(CultivatedMode &&
            (cell.dataset.type === "Plowed" ||
                cell.dataset.type.startsWith("Plowed (Fertilized)"))) &&
                !(fertilizerMode && 
                    (cell.dataset.type.startsWith("Plowed") || 
                cell.dataset.type.startsWith("Cultivated"))) &&
                !(seedMode && 
                    (cell.dataset.type === "Cultivated" || 
                        cell.dataset.type === "Cultivated (Fertilized)"))
                    ){
                        return;
                    }

                    if(cell.dataset.energyUsed === "true") return;

    //ja nav energijas - izsledz paint mode
    if(window.EnergyPanel.current < energyCost)
    {
        paintMode = false;
        NotificationSystem.show("Out of energy!", "error");
        return;

    }

    window.EnergyPanel.consume(energyCost);
    cell.dataset.energyUsed = "true";

     // Ja šūna ir gatava ražas novākšanai
    if (cell.dataset.type === "Grown") 
    {
        harvestCrop(cell);
        return;
    }
       if (seedMode &&
        (cell.dataset.type === "Cultivated" ||
         cell.dataset.type === "Cultivated (Fertilized)")) 
       {
        //plantField(cellId, "carrot");
          changeCellImage(cell, "seeded");  
          return;
       }
        if (editModeActive && cell.dataset.type === "Empty")
            {
                changeCellImage(cell, "Plowed");
                return;

            } 

         if (CultivatedMode &&
            (cell.dataset.type === "Plowed" || cell.dataset.type.startsWith("Plowed (Fertilized)")))
            {
                //plantField(cellId, "carrot");
                 changeCellImage(cell, "Cultivated");
                 return;
            }
           
         if (fertilizerMode && 
            (cell.dataset.type.startsWith("Plowed") || cell.dataset.type.startsWith("Cultivated")))
            {
               // plantField(cellId, "carrot");
                 changeCellImage(cell, "fertilized");
                 return;
            }    
});

//1.pogas darbiba
    cell.addEventListener("mousemove", function(e) 
    {
      
    if (!window.tooltip.classList.contains("show")) return;
         // tooltip vienmēr nedaudz pa labi un zem kursora
           const offsetX = 15; // attālums no kursora X ass
           const offsetY = 15; // attālums no kursora Y ass

           window.tooltip.style.left = e.clientX + offsetX + "px";
           window.tooltip.style.top  = e.clientY + offsetY + "px";
    });
    cell.addEventListener("mouseleave", function()
    {
         window.tooltip.classList.remove("show");
    });
    // 2.pogas uzrok zemi --- paint logika
    cell.addEventListener("mousedown", function() 
    {
        const energyCost = 10;

        if(!canUseEnergy(cell))
        {
            return;
        }
        // Ja enerģija beigusies, paziņo un neļauj klikšķi
         if (window.EnergyPanel.current < energyCost) 
       {
          NotificationSystem.show("Out of power! Cannot use buttons.", "error");
          return;
       }
       paintMode = true;

       if(cell.dataset.energyUsed !== "true")
       {
         window.EnergyPanel.consume(energyCost);
         cell.dataset.energyUsed =  "true";
       }
   
       // Paint mode ieslēgšana uz Grown šūnas
         if (cell.dataset.type === "Grown") 
        {
           harvestCrop(cell); 
            return;
        }
        if(seedMode)
        {
            changeCellImage(cell, "seeded");
            return;
        }
         if (editModeActive && cell.dataset.type === "Empty") 
        {
           changeCellImage(cell, "Plowed");
           return;
        }

       if (CultivatedMode &&
       (cell.dataset.type === "Plowed" || cell.dataset.type.startsWith("Plowed (Fertilized)"))) 
    {
       changeCellImage(cell, "Cultivated");
       return;
    }
    if (fertilizerMode &&
         (cell.dataset.type.startsWith("Plowed") || cell.dataset.type.startsWith("Cultivated"))) 
    {
         changeCellImage(cell, "fertilized");
         return;
    }
});
cell.addEventListener("click", () => 
    {
    if (cell.dataset.type === "Grown") 
    {
       harvestCrop(cell);
      
    }
});
// kad atbrīvo peli jebkurā dokumenta vietā
   document.addEventListener("mouseup", () => {
        paintMode = false;
        allCells.forEach(c => c.dataset.energyUsed = "");});
});
 });
