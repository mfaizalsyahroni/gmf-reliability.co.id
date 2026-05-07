document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('operator-dropdown').addEventListener('change', function () {
        console.log('Operator changed');
        const operator = this.value;
        const aircraftTypeDropdown = document.getElementById('aircraft-type-dropdown');
        
        // Kosongkan dropdown AC Type
        aircraftTypeDropdown.innerHTML = '<option value="">Select Aircraft Type</option>';

        if (operator) {
            // Kirim permintaan AJAX
            fetch(`/get-aircraft-types?operator=${operator}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log(data); // Debugging
                    data.forEach(type => {
                        const option = document.createElement('option');
                        option.value = type.ACType;
                        option.textContent = type.ACType;
                        aircraftTypeDropdown.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching aircraft types:', error));
        }
    });
});