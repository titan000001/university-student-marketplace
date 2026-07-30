/**
 * ============================================================================
 * UniMarket - University Student Marketplace
 * Client-side Application Scripts
 * ============================================================================
 */

"use strict";

document.addEventListener("DOMContentLoaded", () => {
    // DOM Element Selectors
    const categoriesContainer = document.querySelector("#categories-container");
    const statisticsContainer = document.querySelector("#statistics-container");
    const registrationForm = document.querySelector("#registration-form");
    const menuToggle = document.querySelector("#menu-toggle");
    const navigationList = document.querySelector(".main-nav ul");

    // Data Models
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

    /**
     * Renders category cards into the categories section container
     */
    function renderCategories() {
        if (!categoriesContainer) return;

        categoriesContainer.innerHTML = "";
        categories.forEach((category) => {
            const card = document.createElement("article");
            card.className = "category-card";

            const title = document.createElement("h3");
            title.textContent = category.title;

            const desc = document.createElement("p");
            desc.textContent = category.description;

            card.appendChild(title);
            card.appendChild(desc);
            categoriesContainer.appendChild(card);
        });
    }

    /**
     * Renders marketplace statistics cards into the statistics container
     */
    function renderStatistics() {
        if (!statisticsContainer) return;

        statisticsContainer.innerHTML = "";
        statistics.forEach((stat) => {
            const card = document.createElement("article");
            card.className = "stat-card";

            const title = document.createElement("h3");
            title.textContent = stat.title;

            const number = document.createElement("p");
            number.className = "stat-number";
            number.textContent = stat.value;

            card.appendChild(title);
            card.appendChild(number);
            statisticsContainer.appendChild(card);
        });
    }

    /**
     * Initializes mobile navigation toggle behavior with accessibility attributes
     */
    function initMobileNav() {
        if (!menuToggle || !navigationList) return;

        menuToggle.setAttribute("aria-expanded", "false");
        menuToggle.addEventListener("click", () => {
            const isOpen = navigationList.classList.toggle("active");
            menuToggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
        });
    }

    /**
     * Handles student registration form submission and validation
     */
    function initRegistrationForm() {
        if (!registrationForm) return;

        registrationForm.addEventListener("submit", (event) => {
            event.preventDefault();

            const fullNameInput = document.querySelector("#full-name");
            const emailInput = document.querySelector("#email");
            const studentIdInput = document.querySelector("#student-id");
            const departmentInput = document.querySelector("#department");
            const passwordInput = document.querySelector("#password");
            const formMessage = document.querySelector("#form-message");

            const fullName = fullNameInput ? fullNameInput.value.trim() : "";
            const email = emailInput ? emailInput.value.trim() : "";
            const studentId = studentIdInput ? studentIdInput.value.trim() : "";
            const department = departmentInput ? departmentInput.value.trim() : "";
            const password = passwordInput ? passwordInput.value : "";

            if (!formMessage) return;

            // Reset status classes
            formMessage.className = "";

            if (!fullName || !email || !studentId || !department || !password) {
                formMessage.textContent = "Please fill in all fields.";
                formMessage.classList.add("error");
                return;
            }

            if (!email.includes("@")) {
                formMessage.textContent = "Please enter a valid email address.";
                formMessage.classList.add("error");
                return;
            }

            if (password.length < 6) {
                formMessage.textContent = "Password must be at least 6 characters.";
                formMessage.classList.add("error");
                return;
            }

            formMessage.textContent = "Your details were validated. Account creation will be available in a future release.";
            formMessage.classList.add("success");
            registrationForm.reset();
        });
    }

    // Initialize Page Component Operations
    renderCategories();
    renderStatistics();
    initMobileNav();
    initRegistrationForm();
});
