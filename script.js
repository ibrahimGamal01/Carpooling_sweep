let menu = document.querySelector('#menu-icon');
let navList = document.querySelector('.nav-list');

menu.onclick = () => {
    menu.classList.toggle('bx-x');
    navList.classList.toggle('open');
}

// Initialize the map
const map = L.map('map').setView([50.704129514100735, 7.16153100070237], 16);
  
// Add tile layer to the map
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

// Add Geocoder control
const geocoder = L.Control.geocoder({
  defaultMarkGeocode: false,
  collapsed: false,
  placeholder: "Search...",
  errorMessage: "Nothing found.",
  geocoder: L.Control.Geocoder.nominatim()
})
  .on('markgeocode', function (event) {
    const result = event.geocode;
    map.fitBounds(result.bbox);
    L.marker(result.center).addTo(map).bindPopup(result.name || result.properties.formatted).openPopup();
  })
  .addTo(map);

const createRideForm = document.getElementById('create-ride-form');
createRideForm.addEventListener('submit', event => {
  event.preventDefault();
  createRide();
});

function createRide() {
  const pickupLocation = document.getElementById('pickupLocation').value;
  const dropoffLocation = document.getElementById('dropoffLocation').value;
  const seats = document.getElementById('seats').value;

  // Send a request to the server to create a new ride
  fetch('php/create_ride.php', {
    method: 'POST',
    body: JSON.stringify({ pickupLocation, dropoffLocation, seats }), // Updated object keys
  })
    .then(response => response.json())
    .then(data => {
      // Show the ride details on success
      const rideDetails = document.getElementById('ride-details');
      rideDetails.innerHTML = '';

      if (data && data.rideId) {
        const rideDiv = document.createElement('div');
        rideDiv.classList.add('ride-box-container');

        const driverInfo = document.createElement('p');
        driverInfo.classList.add('driver');
        driverInfo.textContent = `Driver: ${data.driver}`;
        rideDiv.appendChild(driverInfo);

        const seatsInfo = document.createElement('p');
        seatsInfo.classList.add('seats');
        seatsInfo.textContent = `Seats: ${data.seats}`;
        rideDiv.appendChild(seatsInfo);

        const pickupInfo = document.createElement('p');
        pickupInfo.textContent = `Pickup: ${data.pickup}`;
        rideDiv.appendChild(pickupInfo);

        const dropoffInfo = document.createElement('p');
        dropoffInfo.textContent = `Dropoff: ${data.dropoff}`;
        rideDiv.appendChild(dropoffInfo);

        // Display the passenger list
        if (data.passengers && data.passengers.length > 0) {
          const passengersDiv = document.createElement('div');
          passengersDiv.classList.add('passengers-box');

          const passengersHeading = document.createElement('h2');
          passengersHeading.textContent = 'Passengers:';
          passengersDiv.appendChild(passengersHeading);

          const passengersList = document.createElement('ul');
          data.passengers.forEach(passenger => {
            const passengerItem = document.createElement('li');
            passengerItem.textContent = `Name: ${passenger.name}, ID: ${passenger.id}`;
            passengersList.appendChild(passengerItem);
          });
          passengersDiv.appendChild(passengersList);

          rideDiv.appendChild(passengersDiv);
        }

        rideDetails.appendChild(rideDiv);
      } else {
        const noRidesMsg = document.createElement('p');
        noRidesMsg.textContent = 'Failed to create a ride.';
        rideDetails.appendChild(noRidesMsg);
      }
    })
    .catch(error => {
      console.error('Error:', error);
    });
}

 // Function to fetch and update ride details
 function updateRideDetails() {
  fetch('php/get_ride_details.php', {
    method: 'POST',
  })
    .then(response => response.json())
    .then(data => {
      const rideDetails = document.getElementById('ride-details');

      if (data && data.length > 0) {
        rideDetails.innerHTML = '';

        data.forEach(rideData => {
          const rideDiv = document.createElement('div');
          rideDiv.classList.add('ride-box-container');

          const driverInfo = document.createElement('p');
          driverInfo.classList.add('driver');
          driverInfo.textContent = `Driver: ${rideData.driver}`;
          rideDiv.appendChild(driverInfo);

          const seatsInfo = document.createElement('p');
          seatsInfo.classList.add('seats');
          seatsInfo.textContent = `Seats: ${rideData.available_seats}`;
          rideDiv.appendChild(seatsInfo);

          const pickupInfo = document.createElement('p');
          pickupInfo.textContent = `Pickup: ${rideData.pickup_location}`;
          rideDiv.appendChild(pickupInfo);

          const dropoffInfo = document.createElement('p');
          dropoffInfo.textContent = `Dropoff: ${rideData.dropoff_location}`;
          rideDiv.appendChild(dropoffInfo);

          // Display the passenger list
          if (rideData.passengers && rideData.passengers.length > 0) {
            const passengersHeading = document.createElement('h2');
            passengersHeading.textContent = 'Passengers:';
            rideDiv.appendChild(passengersHeading);

            const passengersList = document.createElement('ul');
            rideData.passengers.forEach(passenger => {
              const passengerItem = document.createElement('li');
              passengerItem.textContent = `Name: ${passenger.name}, ID: ${passenger.id}`;
              passengersList.appendChild(passengerItem);
            });
            rideDiv.appendChild(passengersList);
          }

          rideDetails.appendChild(rideDiv);
        });
      } else {
        rideDetails.innerHTML = '<p>No ride available.</p>';
      }
    })
    .catch(error => {
      console.error('Error:', error);
    });
}

// Call updateRideDetails initially and then every 10 seconds (10000 milliseconds)
updateRideDetails();
setInterval(updateRideDetails, 10000); // Adjust the interval as needed
