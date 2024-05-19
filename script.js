document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('rideForm');
    const rideName = document.getElementById('rideName');
    const rideDistance = document.getElementById('rideDistance');
    const rideDate = document.getElementById('rideDate');
    const successMessage = document.getElementById('successMessage');
    const totalRideDistance = document.getElementById('totalRideDistance');

    const rideNameError = document.getElementById('rideNameError');
    const rideDistanceError = document.getElementById('rideDistanceError');
    const rideDateError = document.getElementById('rideDateError');
    const emptyData = document.getElementById('emptydata');

    const ridesList = document.getElementById('ridesList');
    const ridesUl = document.getElementById('rides');
    const prevPageButton = document.getElementById('prevPage');
    const nextPageButton = document.getElementById('nextPage');

    const ITEMS_PER_PAGE = 3;
    let currentPage = 1;
    let chartInstance;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        rideNameError.style.display = 'none';
        rideDistanceError.style.display = 'none';
        rideDateError.style.display = 'none';
        emptyData.style.display = 'none';

        let isValid = true;

        if (rideName.value.trim() === '') {
            rideNameError.style.display = 'block';
            isValid = false;
        }

        if (rideDistance.value <= 0 || isNaN(rideDistance.value)) {
            rideDistanceError.style.display = 'block';
            isValid = false;
        }

        const datePattern = /^\d{4}-\d{2}-\d{2}$/;
        if (!datePattern.test(rideDate.value)) {
            rideDateError.style.display = 'block';
            isValid = false;
        }

        if (isValid) {
            const ride = {
                name: rideName.value.trim(),
                distance: parseFloat(rideDistance.value),
                date: rideDate.value,
                username: getUsernameFromQueryParam()
            };

            try {
                await saveRide(ride);
                await displayRides(ride.username);
                await fetchDataAndCreateChart();
                form.reset();
            } catch (error) {
                console.log('Error saving ride');
            }
        }
    });

    const saveRide = async (ride) => {
        const response = await fetch('/api/save_ride.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(ride)
        });
        const result = await response.json();
        successMessage.style.display = 'block';
        successMessage.innerHTML = `<p>${result.message}</p>`
        setTimeout(() => {
            successMessage.style.display = 'none';
        }, 3000);
        if (result.error) {
            throw new Error(result.error);
        }
    };

    const getRides = async (username = null) => {
        let url = '/api/get_rides.php';
        if (username) {
            url += `?username=${encodeURIComponent(username)}`;
        }
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error('Failed to fetch rides');
            }
            const rides = await response.json();
            if (!Array.isArray(rides)) {
                emptyData.style.display = 'block';
                emptyData.innerHTML = `<p>${rides.message}</p>`
                throw new Error('Received invalid data for rides');
            }
            return rides;
        } catch (error) {
            console.log('Error fetching rides');
            return [];
        }
    };
    
    const displayRides = async (username) => {
        const rides = await getRides(username);
        const totalPages = Math.ceil(rides.length / ITEMS_PER_PAGE);

        if (currentPage > totalPages && totalPages > 0) {
            currentPage = totalPages;
        }

        const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
        const endIndex = startIndex + ITEMS_PER_PAGE;
        const ridesToDisplay = rides.slice(startIndex, endIndex);

        ridesUl.innerHTML = '';
        if (ridesToDisplay.length > 0) {
            ridesToDisplay.forEach((ride, index) => {
                const li = document.createElement('li');
                li.innerHTML = `<p>✅ Ride ID: ${ride.id} 🌏 ${ride.name} ▶ ${ride.distance} km on <code>${ride.date}</code></p><hr>`;
                ridesUl.appendChild(li);
            });
        } else {
            ridesUl.innerHTML = '<p>No rides to display</p>';
        }

        ridesList.style.display = rides.length > 0 ? 'block' : 'none';
        prevPageButton.disabled = currentPage === 1;
        nextPageButton.disabled = currentPage === totalPages || totalPages === 0;

        const totalDistance = rides.reduce((total, ride) => {
            return total + (parseFloat(ride.distance) || 0);
        }, 0);

        totalRideDistance.textContent = `Total Distance: ${totalDistance.toFixed(2)} km`;
    };

    const prevPageHandler = async () => {
        if (currentPage > 1) {
            currentPage--;
            await displayRides(getUsernameFromQueryParam());
        }
    };

    const nextPageHandler = async () => {
        const rides = await getRides(getUsernameFromQueryParam());
        const totalPages = Math.ceil(rides.length / ITEMS_PER_PAGE);
        if (currentPage < totalPages) {
            currentPage++;
            await displayRides(getUsernameFromQueryParam());
        }
    };

    prevPageButton.addEventListener('click', prevPageHandler);
    nextPageButton.addEventListener('click', nextPageHandler);

    displayRides(getUsernameFromQueryParam());

    function getUsernameFromQueryParam() {
        const params = new URLSearchParams(window.location.search);
        return params.get('username');
    }

    const fetchDataAndCreateChart = async () => {
        try {
            const data = await fetch('/api/get_rides.php?username=' + getUsernameFromQueryParam())
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch data');
                    }
                    return response.json();
                });
    
            const labels = data.map(ride => ride.date);
            const distances = data.map(ride => ride.distance);
    
            const ctx = document.getElementById('myChart').getContext('2d');
            if (chartInstance) {
                chartInstance.destroy();
            }
            chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    axis: 'y',
                    labels: labels,
                    datasets: [{
                        label: 'Distance Covered',
                        data: distances,
                        font: {
                            family: 'Roboto Mono, monospace',
                        }
                    }]
                },
                options: {
                    indexAxis: 'y',
                    plugins: {
                        title: {
                            display: true,
                            text: 'Distance Covered',
                            font: {
                                family: 'Roboto Mono, monospace',
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Ride Date',
                                font: {
                                    family: 'Roboto Mono, monospace',
                                }
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Distance (km)',
                                font: {
                                    family: 'Roboto Mono, monospace',
                                }
                            }
                        }
                    }
                }
            });
        } catch (error) {
            console.log('Chart: Error fetching data');
        }
    };
    
    fetchDataAndCreateChart();
});
