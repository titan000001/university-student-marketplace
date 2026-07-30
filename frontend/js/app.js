/*
=========================================
UniMarket
Week 3 - Development Package 01
JavaScript Foundation
=========================================
*/

"use strict";

document.addEventListener("DOMContentLoaded", () => {

    console.log("UniMarket JavaScript initialized.");
    const categoriesContainer = document.querySelector("#categories-container");
    const statisticsContainer = document.querySelector("#statistics-container");
    const registrationForm = document.querySelector("#registration-form");
    
    const categories = [
    {
        title: "📚 Textbooks",
        description: "Buy and sell academic books."
    },
    {
        title: "💻 Electronics",
        description: "Laptops, phones and accessories."
    },
    {
        title: "🛏️ Dorm Essentials",
        description: "Furniture and room accessories."
    },
    {
        title: "🎒 Student Services",
        description: "Tutoring, printing and more."
    }
];
    const statistics = [
    {
        title: "Active Listings",
        value: "--"
    },
    {
        title: "Verified Students",
        value: "--"
    },
    {
        title: "Successful Trades",
        value: "--"
    },
    {
        title: "Registered Campuses",
        value: "--"
    }
];

if (categoriesContainer) {

    categories.forEach(category => {

        const card = document.createElement("article");
        card.className = "category-card";

        card.innerHTML = `
            <h3>${category.title}</h3>
            <p>${category.description}</p>
        `;

        categoriesContainer.appendChild(card);

    });

}

    if (statisticsContainer) {

    statistics.forEach(stat => {

        const card = document.createElement("article");
        card.className = "stat-card";

        card.innerHTML = `
            <h3>${stat.title}</h3>
            <p class="stat-number">${stat.value}</p>
        `;

        statisticsContainer.appendChild(card);

    });

}

    const menuToggle = document.querySelector("#menu-toggle");
    const navigationList = document.querySelector(".main-nav ul");

    if (menuToggle && navigationList) {

        menuToggle.addEventListener("click", () => {
            navigationList.classList.toggle("active");
        });

    }

    if (registrationForm) {

        registrationForm.addEventListener("submit", (event) => {

            event.preventDefault();

            const fullName = document.querySelector("#full-name").value;
            const email = document.querySelector("#email").value;
            const studentId = document.querySelector("#student-id").value;
            const department = document.querySelector("#department").value;
            const password = document.querySelector("#password").value;
            const formMessage = document.querySelector("#form-message");

        if (
                fullName === "" ||
                email === "" ||
                studentId === "" ||
                department === "" ||
                password === ""
            ) {

                formMessage.textContent = "Please fill in all fields.";
                return;

            }

            if (!email.includes("@")) {

                formMessage.textContent = "Please enter a valid email address.";
                return;

            }

            if (password.length < 6) {

                formMessage.textContent = "Password must be at least 6 characters.";
                return;

            }

            formMessage.textContent = "Registration Successful!";

            console.log("Student Registration");
            console.log("Name:", fullName);
            console.log("Email:", email);
            console.log("Student ID:", studentId);
            console.log("Department:", department);

        });

    }

});
