const fieldsContainer = document.getElementById("fields");
//tabulas izmers
const fieldSize = [
    [13,10],
    [9,12],
    [13,14],
    [17,12],
    [19,8],
    [14,24],
    [13,15],
    [9,12],
    [9,6]
];


// izveido tabulas atbilstoši tabulu izmēriem (fieldSize)
function generateFields(){
    for (let i = 0; i < fieldSize.length ; i++){
        const table = document.createElement("table");
        table.classList.add(`field${i+1}`);
        table.classList.add("farmland");
        for (let r = 0; r < fieldSize[i][1] ;r++) 
        {
            const tr = document.createElement("tr");
            for (let c = 0; c < fieldSize[i][0]; c++)
            {
                const td = document.createElement ("td");
                td.textContent = '';
                td.classList.add("fieldCell"); 
                td.dataset.type = "Empty"; 
                td.style.position = "relative"; 
                table.style.width = Math.ceil(fieldSize[i][0] * 40 + (fieldSize[i][0])*2) + 'px';
                table.style.height = Math.ceil(fieldSize[i][1] * 40 + (fieldSize[i][1])*2) +'px';

    
                tr.appendChild(td); 
    
            }
            table.appendChild(tr); 
        }
        fieldsContainer.appendChild(table);
    }
}


generateFields();
