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

    const ridesList = document.getElementById('ridesList');
    const ridesUl = document.getElementById('rides');
    const prevPageButton = document.getElementById('prevPage');
    const nextPageButton = document.getElementById('nextPage');

    const ITEMS_PER_PAGE = 3;
    let currentPage = 1;

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        
        // Clear previous error messages
        rideNameError.style.display = 'none';
        rideDistanceError.style.display = 'none';
        rideDateError.style.display = 'none';

        // Form validation
        let isValid = true;

        if (rideName.value.trim() === '') {
            rideNameError.style.display = 'block';
            isValid = false;
        }

        if (rideDistance.value <= 0 || isNaN(rideDistance.value)) {
            rideDistanceError.style.display = 'block';
            isValid = false;
        }

        if (rideDate.value === '') {
            rideDateError.style.display = 'block';
            isValid = false;
        }

        if (isValid) {
            const ride = {
                name: rideName.value.trim(),
                distance: parseFloat(rideDistance.value),
                date: rideDate.value
            };

            saveRide(ride);
            displayRides();
            successMessage.style.display = 'block';
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 3000);

            form.reset();
        }
    });

    const saveRide = (ride) => {
        const rides = getRides();
        rides.unshift(ride); // Add the new ride at the beginning of the array
        localStorage.setItem('rides', JSON.stringify(rides));
    };

    const getRides = () => {
        const rides = localStorage.getItem('rides');
        return rides ? JSON.parse(rides) : [];
    };

    const displayRides = () => {
        const rides = getRides();
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
                li.innerHTML = `<p>✅ Ride No: ${startIndex + index + 1} 🌏 ${ride.name} ▶ ${ride.distance} km on <code>${ride.date}</code></p><hr>`;
                ridesUl.appendChild(li);
            });
        } else {
            ridesUl.innerHTML = '<p>No rides to display</p>';
        }

        ridesList.style.display = rides.length > 0 ? 'block' : 'none';
        prevPageButton.disabled = currentPage === 1;
        nextPageButton.disabled = currentPage === totalPages || totalPages === 0;

        // Calculate total ride distance
        const totalDistance = rides.reduce((total, ride) => total + ride.distance, 0);
        totalRideDistance.textContent = `Total Distance: ${totalDistance.toFixed(2)} km`;
    };

    prevPageButton.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            displayRides();
        }
    });

    nextPageButton.addEventListener('click', () => {
        const rides = getRides();
        const totalPages = Math.ceil(rides.length / ITEMS_PER_PAGE);
        if (currentPage < totalPages) {
            currentPage++;
            displayRides();
        }
    });

    displayRides();
});
