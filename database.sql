-- Table structure for table `users`
CREATE TABLE `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT, -- User ID
  `name` VARCHAR(50) NOT NULL, -- User's name
  `email` VARCHAR(100) NOT NULL, -- User's email
  `password` VARCHAR(255) NOT NULL, -- User's password
  `default_dropoff` VARCHAR(100) NOT NULL, -- User's default drop-off location
  `user_type` ENUM('regular', 'admin') NOT NULL DEFAULT 'regular' -- User type (regular or admin)
);

-- Table structure for table `cars`
CREATE TABLE `cars` (
  `car_id` INT PRIMARY KEY AUTO_INCREMENT, -- Car ID
  `driver_id` INT NOT NULL, -- ID of the car's driver (linked to users table)
  `make` VARCHAR(50) NOT NULL, -- Car make
  `model` VARCHAR(50) NOT NULL, -- Car model
  `year` INT NOT NULL, -- Car manufacturing year
  `color` VARCHAR(50) NOT NULL, -- Car color
  `seating_capacity` INT NOT NULL, -- Car seating capacity
  FOREIGN KEY (`driver_id`) REFERENCES `users`(`id`) -- Foreign key referencing the driver in the users table
);

CREATE TABLE `rides` (
  `ride_id` INT AUTO_INCREMENT PRIMARY KEY,
  `driver_id` INT NOT NULL,
  `pickup_location` VARCHAR(255) NOT NULL,
  `dropoff_location` VARCHAR(255) NOT NULL,
  `pickup_latitude` DECIMAL(10, 8),
  `pickup_longitude` DECIMAL(11, 8),
  `dropoff_latitude` DECIMAL(10, 8),
  `dropoff_longitude` DECIMAL(11, 8),
  `available_seats` INT NOT NULL,
  `status` ENUM('upcoming', 'in progress', 'completed', 'canceled') NOT NULL
);

-- Table structure for table `ride_passengers`
CREATE TABLE `ride_passengers` (
  `ride_id` INT NOT NULL, -- Ride ID (linked to rides table)
  `passenger_id` INT NOT NULL, -- ID of the ride's passenger (linked to users table)
  FOREIGN KEY (`ride_id`) REFERENCES `rides`(`ride_id`), -- Foreign key referencing the ride in the rides table
  FOREIGN KEY (`passenger_id`) REFERENCES `users`(`id`), -- Foreign key referencing the passenger in the users table
  PRIMARY KEY (`ride_id`, `passenger_id`) -- Primary key composed of ride_id and passenger_id
);

-- Table structure for table `bookings`
CREATE TABLE `bookings` (
  `booking_id` INT PRIMARY KEY AUTO_INCREMENT, -- Booking ID
  `ride_id` INT NOT NULL, -- ID of the booked ride (linked to rides table)
  `passenger_id` INT NOT NULL, -- ID of the booking passenger (linked to users table)
  `booking_date` DATE NOT NULL, -- Booking date
  `booking_time` TIME NOT NULL, -- Booking time
  FOREIGN KEY (`ride_id`) REFERENCES `rides`(`ride_id`), -- Foreign key referencing the ride in the rides table
  FOREIGN KEY (`passenger_id`) REFERENCES `users`(`id`) -- Foreign key referencing the passenger in the users table
);

CREATE TABLE `passengers` (
  `passenger_id` INT AUTO_INCREMENT PRIMARY KEY,
  `ride_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  FOREIGN KEY (`ride_id`) REFERENCES `rides` (`ride_id`)
);