const btn = document.querySelector(".pannelCloseButton");

btn.addEventListener("click", () => 
{
    // īss “klikšķa pacelšanās” efekts
    btn.style.transform = "translateY(-50%) translateX(-5px)";
    setTimeout(() => 
    {
        btn.style.transform = "translateY(-50%) translateX(0)";
    }, 100); // atgriežas pēc 0.1s
});
const sidePanel = document.getElementById("sidePanel");
const panelToggle = document.getElementById("sidePanelToggle");

let panelOpen = true;
function resetButtonPosition() 
{
    panelButton.style.top = "calc(50% + 17vw)";
}

sidePanel.addEventListener("mouseenter", () => {
    panelToggle.classList.add("visible");
});


//Klikšķis uz bultiņas
panelToggle.addEventListener("click", () => {
    if (panelOpen) {
        // Aizver paneli
        sidePanel.classList.add("closed");
        panelOpen = false;

        // pēc aizvēršanas, bultiņa paliek redzama un gatava atvērt paneli
        panelToggle.classList.add("visible");
        panelToggle.classList.add("edge");

    } else {
        // Atver paneli
        sidePanel.classList.remove("closed");
        panelOpen = true;

         panelToggle.classList.remove("edge");
          panelToggle.classList.remove("visible");
    }
});

const icons = document.querySelectorAll(".side-panel .icon-box");
const tooltip = document.getElementById("tooltip"); 
tooltip.className = 'tooltip';

//tooltilp / pogas rezimi
window.tooltipActive = false;//kursora logs(1.poga)
window.editModeActive = false;//sunu redigesana(2.poga)
window.CultivatedMode = false;//3.poga
window.fertilizerMode = false;//4. poga
window.paintMode = false; // paint režīms
window.seedMode = false; //5. poga

//1.poga - (neatkariga)
icons[0].addEventListener("click", function()
{
    window.tooltipActive = !window.tooltipActive; // ieslēdz / izslēdz
    icons[0].classList.toggle("active"); // pārslēdz krāsu
});

// funkcija, lai izslēdz konfliktējošās pogas (2,3,4,5)
function disableOtherModes(activeIndex)
{
    const modeFlags = [
        window.editModeActive, 
        window.CultivatedMode, 
        window.fertilizerMode, 
        window.seedMode];
    const modeIcons = [icons[1], icons[2], icons[3], icons[4]];

    modeFlags.forEach((mode, i) => 
    {
        if (i !== activeIndex && mode) 
        {
            if (i === 0) window.editModeActive = false;
            if (i === 1) window.CultivatedMode = false;
            if (i === 2) window.fertilizerMode = false;
            if (i === 3) window.seedMode = false;

            modeIcons[i].classList.remove("active");
        }
    });
}
// 2. poga - attelu maina
icons[1].addEventListener("click", function() {
    window.editModeActive = !window.editModeActive;
    icons[1].classList.toggle("active");

    if (window.editModeActive) 
    {
        disableOtherModes(0); // izslēdz 3. un 4.
    } else 
    {
        // ja izslēdz 2., automātiski izslēdz 3. ja tā ieslēgta
        if (window.CultivatedMode) 
        {
            window.CultivatedMode = false;
            icons[2].classList.remove("active");
        }
    }
});
//3.poga - strada tikai uz sunam kas tikai mainitas jau ar 2. pogu
icons[2].addEventListener("click", function() {
    // pārbauda, vai vismaz viena šūna ir Plowed
    const hasEditCell = Array.from(document.querySelectorAll("#fields td"))
    .some(cell => cell.dataset.type === "Plowed");
    if (!hasEditCell) 
    {
        NotificationSystem.show("First use button 2 on at least one cell!", 
            "warning"
        );
        return;
    }

    window.CultivatedMode = !window.CultivatedMode;
    icons[2].classList.toggle("active");

    // ja 3.poga ieslēgta, izslēdz 2. un 4.
    if (window.CultivatedMode) 
    {
        disableOtherModes(1); 
    }
});
//4. poga - jastrada ar sunam kas tika mainitas ar 2. un 3. pogu
icons[3].addEventListener("click", function()
{
   const hasPlowedOrCultivated = Array.from(document.querySelectorAll("#fields td"))
        .some(cell => cell.dataset.type === "Plowed" || cell.dataset.type === "Cultivated");
    if (!hasPlowedOrCultivated) return NotificationSystem.show("First use button 2 or 3 on at least one cell!",
        "info"
    );

    window.fertilizerMode = !window.fertilizerMode;
    icons[3].classList.toggle("active");

    if (window.fertilizerMode) disableOtherModes(2);
});
// 5. poga – strādā TIKAI uz Cultivated vai Cultivated+fertilizer
icons[4].addEventListener("click", function () 
{

    // Pārbauda, vai kartē vispār ir kultivētas šūnas
    const hasCultivated = Array.from(document.querySelectorAll("#fields td"))
    .some(cell => cell.dataset.type.startsWith("Cultivated"));

    if (!hasCultivated) 
    {
        NotificationSystem.show("This button can only be used on cultivated cells (button 3)!",
            "error"
        );
        return;
    }   
    window.seedMode = !window.seedMode;
    icons[4].classList.toggle("active");

    if (window.seedMode) disableOtherModes(3);
});
