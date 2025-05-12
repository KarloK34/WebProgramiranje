let originalData = [];
let displayedWeatherData = []; 
let userPlanData = []; 

document.addEventListener('DOMContentLoaded', () => {
    if (typeof initialWeatherData !== 'undefined' && Array.isArray(initialWeatherData)) {
        originalData = initialWeatherData.map(row => ({
            'id': row.id, 
            'Date': row.date,
            'Season': row.season,
            'Weather Type': row.weather_type,
            'Temperature (°C)': parseFloat(row.temperature), 
            'Precipitation (mm)': parseFloat(row.precipitation), 
            'Location': row.location,
        }));
        displayedWeatherData = [...originalData];
        if (originalData.length > 0) {
            populateTable(displayedWeatherData, 'weather-data');
        } else {
            displayMessageInTable('weather-data', 'Nema dostupnih vremenskih podataka iz baze.', 8);
        }
    } else {
        console.error("initialWeatherData nije definiran ili nije polje.");
        displayMessageInTable('weather-data', 'Greška pri učitavanju podataka s poslužitelja.', 8);
    }

    if (typeof initialUserPlan !== 'undefined' && Array.isArray(initialUserPlan)) {
        userPlanData = initialUserPlan.map(item => ({
            plan_id: item.plan_id, 
            weather_data_id: item.weather_data_id,
            'id': item.weather_data_id, 
            'Date': item.date,
            'Season': item.season,
            'Weather Type': item.weather_type,
            'Temperature (°C)': parseFloat(item.temperature),
            'Precipitation (mm)': parseFloat(item.precipitation),
            'Location': item.location
        }));
        if (userPlanData.length > 0) {
            populateTable(userPlanData, 'plan-table', true);
        }
        updatePlanMessage();
    } else {
        console.warn("initialUserPlan nije definiran ili je prazan.");
        updatePlanMessage(); 
    }

    const filterButton = document.getElementById('primijeni-filtere');
    if (filterButton) filterButton.addEventListener('click', applyFilters);

    const tempMinSlider = document.getElementById('filter-temperature-min');
    const tempMinValueSpan = document.getElementById('temperature-min-value');
    if (tempMinSlider && tempMinValueSpan) {
        tempMinSlider.addEventListener('input', () => tempMinValueSpan.textContent = tempMinSlider.value);
        tempMinValueSpan.textContent = tempMinSlider.value;
    }

    const tempMaxSlider = document.getElementById('filter-temperature-max');
    const tempMaxValueSpan = document.getElementById('temperature-max-value');
    if (tempMaxSlider && tempMaxValueSpan) {
        tempMaxSlider.addEventListener('input', () => tempMaxValueSpan.textContent = tempMaxSlider.value);
        tempMaxValueSpan.textContent = tempMaxSlider.value;
    }

    const addToPlanButton = document.getElementById('add-to-plan');
    if (addToPlanButton) addToPlanButton.addEventListener('click', addSelectedToPlan);

    const previewPlanButton = document.getElementById('preview-plan');
    if (previewPlanButton) previewPlanButton.addEventListener('click', previewPlan);
});

function displayMessageInTable(tableId, message, colspan) {
    const table = document.getElementById(tableId);
    if (table) {
        let tbody = table.querySelector('tbody');
        if (!tbody) {
            tbody = document.createElement('tbody');
            table.appendChild(tbody);
        }
        tbody.innerHTML = `<tr><td colspan="${colspan}">${message}</td></tr>`;
    }
}

function populateTable(data, tableId, isPlanTable = false) {
    const table = document.getElementById(tableId);
    if (!table) {
        console.error(`Element tablice '${tableId}' nije pronađen.`);
        return;
    }
    table.innerHTML = ''; 

    if (!data || data.length === 0) {
        const colCount = (originalData.length > 0 && Object.keys(originalData[0]).length > 0)
                         ? Object.keys(originalData[0]).length + 1 
                         : 8; 
        table.innerHTML = `<thead><tr><th colspan="${colCount}">${isPlanTable ? 'Plan je prazan.' : 'Nema podataka za prikazane filtere.'}</th></tr></thead><tbody></tbody>`;
        return;
    }

    const headers = Object.keys(data[0]).filter(h => h !== 'id' && h !== 'plan_id' && h !== 'weather_data_id');

    const thead = table.createTHead();
    const headerRow = thead.insertRow();

    if (!isPlanTable) {
        const selectAllTh = document.createElement('th');
        const selectAllCheckbox = document.createElement('input');
        selectAllCheckbox.type = 'checkbox';
        selectAllCheckbox.title = 'Odaberi sve';
        selectAllCheckbox.id = `select-all-${tableId}`;
        selectAllCheckbox.addEventListener('change', (event) => {
            const checkboxes = table.querySelectorAll('tbody input[type="checkbox"].day-checkbox');
            checkboxes.forEach(cb => cb.checked = event.target.checked);
        });
        selectAllTh.appendChild(selectAllCheckbox);
        headerRow.appendChild(selectAllTh);
    } else {
        const th = document.createElement('th');
        th.textContent = 'Akcija';
        headerRow.appendChild(th);
    }

    headers.forEach(headerText => {
        const th = document.createElement('th');
        th.textContent = headerText;
        headerRow.appendChild(th);
    });

    const tbody = table.createTBody();
    data.forEach((rowItem, rowIndex) => {
        const tr = tbody.insertRow();

        if (!isPlanTable) {
            const selectTd = tr.insertCell();
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.value = rowIndex;
            checkbox.classList.add('day-checkbox');
            selectTd.appendChild(checkbox);
        } else {
            const actionTd = tr.insertCell();
            const removeButton = document.createElement('button');
            removeButton.textContent = 'Ukloni';
            removeButton.classList.add('remove-from-plan-btn');
            removeButton.dataset.planId = rowItem.plan_id;
            removeButton.addEventListener('click', () => removeFromPlan(rowItem.plan_id));
            actionTd.appendChild(removeButton);
        }

        headers.forEach(header => {
            const td = tr.insertCell();
            td.textContent = rowItem[header] !== null && rowItem[header] !== undefined ? rowItem[header] : '';
        });
    });
}

function applyFilters() {
    const seasonFilter = document.getElementById('filter-season').value;
    const weatherTypeFilter = document.querySelector('input[name="weather-type"]:checked').value;
    const tempMinFilter = parseFloat(document.getElementById('filter-temperature-min').value);
    const tempMaxFilter = parseFloat(document.getElementById('filter-temperature-max').value);

    if (!originalData || originalData.length === 0) {
        displayedWeatherData = [];
        populateTable(displayedWeatherData, 'weather-data');
        return;
    }

    displayedWeatherData = originalData.filter(row => {
        let matchesSeason = !seasonFilter || row.Season === seasonFilter;
        let matchesWeatherType = !weatherTypeFilter || row['Weather Type'] === weatherTypeFilter;
        
        const rowTemp = row['Temperature (°C)'];
        let matchesTemperature = (typeof rowTemp === 'number' && !isNaN(rowTemp))
            ? (rowTemp >= tempMinFilter && rowTemp <= tempMaxFilter)
            : !(seasonFilter || weatherTypeFilter);

        return matchesSeason && matchesWeatherType && matchesTemperature;
    });

    populateTable(displayedWeatherData, 'weather-data');
    const selectAllCb = document.getElementById('select-all-weather-data');
    if (selectAllCb) selectAllCb.checked = false;
}

async function addSelectedToPlan() {
    const selectedCheckboxes = document.querySelectorAll('#weather-data tbody .day-checkbox:checked');
    const planFeedbackDiv = document.getElementById('plan-feedback');
    planFeedbackDiv.innerHTML = '';

    if (selectedCheckboxes.length === 0) {
        alert("Niste odabrali niti jedan dan za dodavanje u plan.");
        return;
    }

    let itemsProcessedCount = 0;
    let successfulAdds = 0;

    for (const checkbox of selectedCheckboxes) {
        const rowIndex = parseInt(checkbox.value);
        if (rowIndex >= 0 && rowIndex < displayedWeatherData.length) {
            const selectedDayData = displayedWeatherData[rowIndex];
            const weatherId = selectedDayData.id;

            const alreadyInClientPlan = userPlanData.some(planItem => planItem.weather_data_id === weatherId);
            if (alreadyInClientPlan) {
                planFeedbackDiv.innerHTML += `<p style="color:orange;">Dan ${selectedDayData.Date} (${selectedDayData.Location}) je već u vašem planu.</p>`;
                checkbox.checked = false;
                itemsProcessedCount++;
                if (itemsProcessedCount === selectedCheckboxes.length && successfulAdds > 0) {
                     populateTable(userPlanData, 'plan-table', true);
                     updatePlanMessage();
                }
                continue;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'add');
                formData.append('weather_data_id', weatherId);

                const response = await fetch('plan_handler.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    const newPlanItem = {
                        plan_id: result.plan_id,
                        weather_data_id: weatherId,
                        ...selectedDayData
                    };
                    delete newPlanItem.id;
                    newPlanItem.id = weatherId;

                    userPlanData.push(newPlanItem);
                    successfulAdds++;
                    planFeedbackDiv.innerHTML += `<p style="color:green;">Dan ${selectedDayData.Date} (${selectedDayData.Location}) dodan u plan.</p>`;
                    if (result.warning) {
                        planFeedbackDiv.innerHTML += `<p style="color:red; font-weight:bold;">${result.warning}</p>`;
                    }
                } else {
                    planFeedbackDiv.innerHTML += `<p style="color:red;">Greška za dan ${selectedDayData.Date}: ${result.message || 'Nepoznata greška.'}</p>`;
                }
            } catch (error) {
                console.error('Error adding to plan:', error);
                planFeedbackDiv.innerHTML += `<p style="color:red;">Greška pri komunikaciji sa serverom za dan ${selectedDayData.Date}.</p>`;
            }
            checkbox.checked = false;
        }
        itemsProcessedCount++;
    }

    if (successfulAdds > 0 || itemsProcessedCount > 0) {
        populateTable(userPlanData, 'plan-table', true);
        updatePlanMessage();
    }

    const selectAllCb = document.getElementById('select-all-weather-data');
    if (selectAllCb) selectAllCb.checked = false;
}

async function removeFromPlan(planId) {
    if (!planId) return;
    const planFeedbackDiv = document.getElementById('plan-feedback');
    planFeedbackDiv.innerHTML = '';

    try {
        const formData = new FormData();
        formData.append('action', 'remove');
        formData.append('plan_id', planId);

        const response = await fetch('plan_handler.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            userPlanData = userPlanData.filter(item => item.plan_id !== planId);
            populateTable(userPlanData, 'plan-table', true);
            updatePlanMessage();
            planFeedbackDiv.innerHTML = `<p style="color:green;">${result.message || 'Dan uklonjen iz plana.'}</p>`;
        } else {
            planFeedbackDiv.innerHTML = `<p style="color:red;">${result.message || 'Greška pri uklanjanju.'}</p>`;
        }
    } catch (error) {
        console.error('Error removing from plan:', error);
        planFeedbackDiv.innerHTML = `<p style="color:red;">Greška pri komunikaciji sa serverom.</p>`;
    }
}

function updatePlanMessage() {
    const planMessage = document.getElementById('plan-message');
    if (!planMessage) return;

    if (userPlanData.length === 0) {
        planMessage.textContent = "Vaš plan je trenutno prazan.";
    } else {
        const totalDays = userPlanData.length;
        let totalTemp = 0;
        let validTempCount = 0;
        userPlanData.forEach(item => {
            const temp = parseFloat(item['Temperature (°C)']);
            if (!isNaN(temp)) {
                totalTemp += temp;
                validTempCount++;
            }
        });
        const avgTemp = validTempCount > 0 ? (totalTemp / validTempCount).toFixed(1) : "N/A";
        planMessage.textContent = `Vaš plan sadrži ${totalDays} dana. Prosječna temperatura: ${avgTemp}°C.`;
    }
}

function previewPlan() {
    if (userPlanData.length === 0) {
        alert("Vaš plan je prazan. Dodajte dane iz gornje tablice.");
        return;
    }

    let planDetails = "Pregled Plana:\n\n";
    userPlanData.forEach((item, index) => {
        planDetails += `${index + 1}. Datum: ${item.Date}, Lokacija: ${item.Location}, Vrijeme: ${item['Weather Type']}, Temp: ${item['Temperature (°C)']}°C\n`;
    });

    const totalDays = userPlanData.length;
    let totalTemp = 0;
    let validTempCount = 0;
    userPlanData.forEach(item => {
        const temp = parseFloat(item['Temperature (°C)']);
        if (!isNaN(temp)) {
            totalTemp += temp;
            validTempCount++;
        }
    });
    const avgTemp = validTempCount > 0 ? (totalTemp / validTempCount).toFixed(1) : "N/A";
    planDetails += `\nUkupno dana: ${totalDays}. Prosječna temperatura: ${avgTemp}°C.`;

    alert(planDetails);
}