let playerMoney = 0;//speletaja nauda
window.playerMoney = 0;
const moneyBox = document.getElementById("moneyBox");
const sellModal = document.getElementById("sellModal");
const modalHarvestCount = document.getElementById("modalHarvestCount");
const sellAmountInput = document.getElementById("sellAmount");
const sellButton = document.getElementById("sellButton");
const closeSellModal = document.getElementById("closeSellModal");
//aizversana ar krustinu
document.getElementById("closeSellModal").addEventListener("click", () => {
    sellModal.style.display = "none";
    sellModal.classList.remove("show");
});
//aizversana arpus modal
document.addEventListener("click", (e) => {
   if(
    sellModal.style.display === "block" && 
    !sellModal.contains(e.target) &&
    e.target.id === "harvestCounter"
   ){
    sellModal.style.display ="none";
    sellModal.classList.remove("show");
   }
});
function loadMoney() {
    fetch("/modules/api.php", {
        method: "POST",
        body: JSON.stringify({
            action: "getMoney",
            userId: window.App.userId
        })
    });
}
function updateMoneyDisplay()
{
    moneyBox.textContent = `$${playerMoney}`;

}
function openSellModal()
{
    modalHarvestCount.textContent = harvestedCount;
    sellAmountInput.value = "";
    sellModal.style.display = "block";
}
function closeSellModalFunc()
{
   sellModal.style.display = "none";
}
//klikskis uz skaititaja - atver modal logu
moneyBox.addEventListener("click", () => {
    openSellModal();
});
closeSellModal.addEventListener("click", () => {
    closeSellModalFunc();
});
//pardosana
sellButton.addEventListener("click", () => {
    let sellAmount = parseInt(sellAmountInput.value);
    if(isNaN(sellAmount) || sellAmount < 1) return  NotificationSystem.show("Enter a valid number!", "warning");
    if(sellAmount > harvestedCount) return NotificationSystem.show("There isn't that much harvest!", "warning");

    const pricePerUnit = 2; //2$
    playerMoney += sellAmount * pricePerUnit;
    harvestedCount -= sellAmount;

    //atjauno
    modalHarvestCount.textContent = harvestedCount;
    document.getElementById("harvestCounter").textContent = harvestedCount;
    updateMoneyDisplay();
    //aizver logu
    closeSellModalFunc();
});