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

    const menuToggle = document.querySelector("#menu-toggle");
    const navigationList = document.querySelector(".main-nav ul");

    if (menuToggle && navigationList) {

        menuToggle.addEventListener("click", () => {
            navigationList.classList.toggle("active");
        });

    }

});
