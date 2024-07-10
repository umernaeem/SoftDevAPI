# Cake Day Laravel API

The repository contains all the frontend files Laravel API

## Requirements
Everyone deserves to celebrate their birthday with cake. We would like you to build a small application to enable us to keep track of developer birthdays and cakes. This application should have a Laravel backend and a Vue frontend. 

The application should receive as an input a text file containing a list of developers and their dates of birth, in the following format with one entry per line:

[Person Name],[Date of Birth (yyyy-mm-dd)]

For example:

Luna,2021-09-21
Freya,2024-01-20 

We want to show off birthday information on a large screen monitor in our office, so the developers know when Cake Days are coming. Using Vue and Tailwind, design an aƩracƟve UI to display the following information for the current year:

Date | Number of Small Cakes | Number of Large Cakes | Names of People Getting Cake

“Cake Days” are calculated according to the following rules: 

- A small cake is provided on the employee’s first working day aŌer their birthday.
- All employees get their birthday off.
- The office is closed on weekends, Christmas Day, Boxing Day, and New Year’s Day.
- If the office is closed on an employee’s birthday, they get the next working day off.
- If two or more Cake Days coincide, we instead provide one large cake to share.
- If there is to be cake two days in a row, we instead provide one large cake on the second day.
- For health reasons, the day after each cake must be cake-free. Any cakes due on a cake-free day are postponed to the next working day.
- There is never more than one cake per day. 

## Solution
The solution to the problem is to create a frontend Vue appalication that will consume the laravel API.
## Demo
See [Demo](https://vue.iamtechwriter.com/).
The demo is fully working app which is consuming laravel api
## Important Files to see

- [CakeDaysResource.php](https://github.com/umernaeem/SoftDevAPI/blob/main/app/Http/Resources/CakeDaysResource.php)
- [CakeDaysController](https://github.com/umernaeem/SoftDevAPI/blob/main/app/Http/Controllers/Api/V1/CakeDaysController.php)
- [Api.php](https://github.com/umernaeem/SoftDevAPI/blob/main/routes/api.php)
