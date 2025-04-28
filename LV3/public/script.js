let planData = []; 
let originalData = []; 

fetch('vrijeme.csv')
.then(res => res.text())
.then(csv => {
    const rezultat = Papa.parse(csv, {
        header: true,
        skipEmptyLines: true
    });
    originalData = rezultat.data.map(row => ({
        datum: row.ID, 
        temperatura: parseFloat(row.Temperature),
        vlaznost: parseFloat(row.Humidity),
        brzina_vjetra: parseFloat(row['Wind Speed']),
        oborine: parseFloat(row['Precipitation (%)']),
        oblacnost: row['Cloud Cover'],
        pritisak: parseFloat(row['Atmospheric Pressure']),
        uv_indeks: parseInt(row['UV Index']),
        sezona: row.Season,
        vidljivost: parseFloat(row['Visibility (km)']),
        lokacija: row.Location,
        tip: row['Weather Type'],
    }));

    console.log("Original Data:", originalData);

    const tableElement = document.getElementById('weather-data');
    const planTableElement = document.getElementById('plan-table'); 

    if (tableElement && planTableElement) {
        createTableHeader(tableElement, true); 
        createTableHeader(planTableElement, false); 
    } else {
        console.error("Table element 'weather-data' or 'plan-table' not found.");
        return;
    }

    const first20 = originalData.slice(0, 20);
    showDataInTable(first20, 'weather-data');
    showPlanData(); 

    const filterButton = document.getElementById('primijeni-filtere');
    const seasonSelect = document.getElementById('filter-season');
    const tempMinSlider = document.getElementById('filter-temperature-min');
    const tempMaxSlider = document.getElementById('filter-temperature-max');
    const tempMinValueSpan = document.getElementById('temperature-min-value');
    const tempMaxValueSpan = document.getElementById('temperature-max-value');

    tempMinSlider.addEventListener('input', () => tempMinValueSpan.textContent = tempMinSlider.value);
    tempMaxSlider.addEventListener('input', () => tempMaxValueSpan.textContent = tempMaxSlider.value);

    filterButton.addEventListener('click', () => {
        const selectedSeason = seasonSelect.value;
        const selectedWeatherType = document.querySelector('input[name="weather-type"]:checked').value;
        const minTemp = parseFloat(tempMinSlider.value);
        const maxTemp = parseFloat(tempMaxSlider.value);
        filterAndDisplayData(originalData, selectedSeason, selectedWeatherType, minTemp, maxTemp);
    });

    const addToPlanButton = document.getElementById('add-to-plan');
    addToPlanButton.addEventListener('click', () => {
        const checkboxes = document.querySelectorAll('#weather-data tbody input[type="checkbox"]:checked');
        checkboxes.forEach(checkbox => {
            const rowId = checkbox.closest('tr').dataset.rowId; 
            const dataToAdd = originalData.find(item => item.datum === rowId);
            if (dataToAdd && !planData.some(item => item.datum === rowId)) {
                planData.push(dataToAdd);
            }
        });
        showPlanData(); 
        checkboxes.forEach(checkbox => checkbox.checked = false);
        console.log("Current Plan:", planData);
    });

    planTableElement.addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-button')) {
            const rowIdToRemove = event.target.closest('tr').dataset.rowId;
            planData = planData.filter(item => item.datum !== rowIdToRemove); 
            showPlanData();
        }
    });

    const previewPlanButton = document.getElementById('preview-plan');
    const planMessageElement = document.getElementById('plan-message');
    previewPlanButton.addEventListener('click', () => {
        if (planData.length === 0) {
            planMessageElement.textContent = "Your plan is empty. Please add some days.";
            planMessageElement.style.color = 'orange';
        } else {
            planMessageElement.textContent = `You've successfully planned ${planData.length} day(s) for your activities!`;
            planMessageElement.style.color = 'green';
            planData = [];
            showPlanData();
        }
        setTimeout(() => { planMessageElement.textContent = ''; }, 5000);
    });

}); 

function createTableHeader(tableElement, addCheckboxColumn) {
    const existingThead = tableElement.querySelector('thead');
    if (existingThead) {
        existingThead.remove();
    }

    const thead = tableElement.createTHead();
    const headerRow = thead.insertRow();

    if (addCheckboxColumn) {
        const thCheckbox = document.createElement('th');
        thCheckbox.textContent = "Select"; 
        headerRow.appendChild(thCheckbox);
    }

    const headerTitles = [
        "Datum (ID)", "Temperature (°C)", "Humidity (%)", "Wind Speed (km/h)",
        "Precipitation (%)", "Cloud Cover", "Atmospheric Pressure", "UV Index",
        "Season", "Visibility (km)", "Location", "Weather Type"
    ];

    headerTitles.forEach(title => {
        const th = document.createElement('th');
        th.textContent = title;
        headerRow.appendChild(th);
    });

    if (!addCheckboxColumn) { 
         const thActions = document.createElement('th');
         thActions.textContent = "Actions";
         headerRow.appendChild(thActions);
    }
}

function showDataInTable(dataToShow, tableId) {
    const tableElement = document.getElementById(tableId);
    if (!tableElement || tableElement.tagName !== 'TABLE') {
        console.error(`Table element with id "${tableId}" not found or is not a TABLE.`);
        return;
    }

    let tbody = tableElement.querySelector('tbody');
    if (!tbody) {
        tbody = tableElement.createTBody();
    }
    tbody.innerHTML = '';

    dataToShow.forEach(row => {
        const tr = tbody.insertRow();
        tr.dataset.rowId = row.datum; 

        const tdCheckbox = tr.insertCell();
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.value = row.datum; 
        tdCheckbox.appendChild(checkbox);

        const orderedValues = [
            row.datum, row.temperatura, row.vlaznost, row.brzina_vjetra,
            row.oborine, row.oblacnost, row.pritisak, row.uv_indeks,
            row.sezona, row.vidljivost, row.lokacija, row.tip
        ];
        orderedValues.forEach(text => {
            const td = tr.insertCell();
            td.textContent = text !== null && text !== undefined ? text : '';
        });
    });
}

function showPlanData() {
    const tableElement = document.getElementById('plan-table');
    if (!tableElement || tableElement.tagName !== 'TABLE') {
        console.error(`Table element with id "plan-table" not found or is not a TABLE.`);
        return;
    }

    let tbody = tableElement.querySelector('tbody');
    if (!tbody) {
        tbody = tableElement.createTBody();
    }
    tbody.innerHTML = ''; 

    planData.forEach(row => {
        const tr = tbody.insertRow();
        tr.dataset.rowId = row.datum; 

        const orderedValues = [
            row.datum, row.temperatura, row.vlaznost, row.brzina_vjetra,
            row.oborine, row.oblacnost, row.pritisak, row.uv_indeks,
            row.sezona, row.vidljivost, row.lokacija, row.tip
        ];
        orderedValues.forEach(text => {
            const td = tr.insertCell();
            td.textContent = text !== null && text !== undefined ? text : '';
        });

        const tdAction = tr.insertCell();
        const removeButton = document.createElement('button');
        removeButton.innerHTML = '&#128465;'; 
        removeButton.classList.add('remove-button'); 
        tdAction.appendChild(removeButton);
    });
}

function filterAndDisplayData(allData, season, weatherType, minTemp, maxTemp) {
    const filtered = allData.filter(row => {
        const seasonMatch = !season || (row && row.sezona === season);
        const weatherMatch = !weatherType || weatherType === "" || (row && row.tip === weatherType);
        const tempMatch = row && typeof row.temperatura === 'number' && !isNaN(row.temperatura) &&
                          row.temperatura >= minTemp && row.temperatura <= maxTemp;
        return seasonMatch && weatherMatch && tempMatch;
    });

    console.log("Filtered Data:", filtered);
    showDataInTable(filtered, 'weather-data'); 
}