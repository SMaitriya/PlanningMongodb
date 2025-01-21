## Project Overview:

This project is a web application for chore planning and management, developed using PHP with the MVC (Model-View-Controller) architecture and MongoDB Atlas for the database. The system facilitates the organization of peeling chores by year and week, assigning individuals to specific weeks, and providing statistical insights.



### Features

- Home Page:

The user begins by selecting a specific year.

A detailed weekly schedule for the chosen year is displayed, showing the individuals assigned to each week.

At the bottom of the page, yearly statistics are presented in ascending order, reflecting the contributions of each individual.

![home](https://raw.githubusercontent.com/SMaitriya/Portfolio/main/public/images/potato.php/home.png)


- User Authentication:

Secure login system with username and hashed password authentication.

Only authenticated users can make modifications to the chore schedule.


![connec](https://raw.githubusercontent.com/SMaitriya/Portfolio/main/public/images/potato.php/connexion.png)


- Schedule Modification:

Once logged in, users can edit the weekly assignments for any selected year.

After saving changes, the updated schedule is reflected in the yearly statistics.

- Real-Time Updates:

Modifications to the schedule immediately update the statistics displayed on the homepage and other relevant pages.


![mod](https://raw.githubusercontent.com/SMaitriya/Portfolio/main/public/images/potato.php/modifier.png)


### Technical Highlights:

- PHP MVC Architecture:

The application is structured with clear separation of concerns: Models handle data, Views manage the user interface, and Controllers process user input.

- MongoDB Atlas Integration:

The database is hosted on MongoDB Atlas, ensuring scalability and efficient data management.

The chore data, user information, and statistics are stored and retrieved seamlessly.

- Statistics Generation:

The system calculates and displays yearly statistics in ascending order based on user contributions.

MongoDB queries are used to perform the calculations and fetch the required data efficiently.

Updates to the schedule automatically trigger recalculations and update the statistics.

- Secure Authentication:

Passwords are hashed for secure storage.

The authentication system ensures only authorized users can make changes.

