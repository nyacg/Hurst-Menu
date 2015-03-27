# Hurst-Menu
A system I built for my A-Level Computing project, it aimed to reduce food waste at school.

The issue was that the catering department could only roughly estimate the number of meal attendees (especially for evening meals and weekends) and they didn't have much data to go on. I set out to fix this whilst also providing a number of other features that would benefit the students and the caterers. 

The system created had three components:
1. The management console for the catering manager
It provides:
- uploading of the menu as their standard excel format and parsing into the database
- viewing and analysis of data collected (in detailed and dashboard view)

2. A mobile and tablet responsive web app for the student menu system
It provides:
- Past, present and future menu access with gesture control to change day
- Feedback message submission
- Voting on preferred menu items
- Submission of supper attendance for the coming week

3. A Raspberry Pi and ultrasonic sensor based person counter to record the number of attendees for each meal everyday

All the data collected is submitted to a MySQL database and then can be analysed to predict meal attendance.

There are a number of changes I would make now I am more experienced but the system works and it is relatively transferable to other places with similar catering situations.
