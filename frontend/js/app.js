/**
 * UniMarket - University Student Marketplace
 * Frontend JavaScript Application Engine (Bulletproof SVG Edition)
 */

"use strict";

document.addEventListener("DOMContentLoaded", () => {
    console.log("UniMarket JavaScript initialized.");

    // DOM Element Selectors
    const categoriesContainer = document.querySelector("#categories-container");
    const statisticsContainer = document.querySelector("#statistics-container");
    const registrationForm = document.querySelector("#registration-form");
    const menuToggle = document.querySelector("#menu-toggle");
    const navigationList = document.querySelector(".main-nav ul");

    // Data Models with Generated Image Assets & Explicit SVG Attributes
    const categories = [
        {
            title: "Textbooks & Guides",
            description: "Buy and sell course books, lab manuals, and exam prep guides.",
            image: "../images/cat-textbooks.png",
            icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 19.5A2.5 2.5 0 0 0 6.5 22H20V2H6.5A2.5 2.5 0 0 0 4 4.5v15z"/></svg>`
        },
        {
            title: "Student Electronics",
            description: "Laptops, smartphones, graphing calculators, and accessories.",
            image: "../images/cat-electronics.png",
            icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><path d="M20 16V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12m16 0H4m16 0a2 2 0 0 1 2 2v1H2v-1a2 2 0 0 1 2-2"/></svg>`
        },
        {
            title: "Dorm Essentials",
            description: "Desk lamps, chairs, mini storage, and room decor.",
            image: "../images/cat-dorm.png",
            icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>`
        },
        {
            title: "Campus Services",
            description: "Peer tutoring, lab assistance, printing, and study support.",
            image: "../images/cat-services.png",
            icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>`
        }
    ];

    const statistics = [
        {
            title: "Active Listings",
            value: "250+",
            icon: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-lg"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>`
        },
        {
            title: "Verified Students",
            value: "1,200+",
            icon: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-lg"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`
        },
        {
            title: "Successful Trades",
            value: "850+",
            icon: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-lg"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`
        },
        {
            title: "Registered Campuses",
            value: "12",
            icon: `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-lg"><path d="M3 21h18M3 7v14M21 7v14M6 21V11M18 21V11M9 21v-4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v4M12 3L2 7h20L12 3z"/></svg>`
        }
    ];

    // Render Featured Categories with Images and SVG Badges
    if (categoriesContainer) {
        categoriesContainer.innerHTML = "";
        categories.forEach((category) => {
            const card = document.createElement("article");
            card.className = "category-card";

            card.innerHTML = `
                <div class="category-img-wrapper">
                    <img src="${category.image}" alt="${category.title}" class="category-img" loading="lazy">
                </div>
                <div class="category-content">
                    <h3>${category.icon} ${category.title}</h3>
                    <p>${category.description}</p>
                    <a href="login.php" class="category-link">
                        Browse Listings
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-icon svg-icon-sm"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </div>
            `;

            categoriesContainer.appendChild(card);
        });
    }

    // Render Marketplace Statistics with Metric Icons
    if (statisticsContainer) {
        statisticsContainer.innerHTML = "";
        statistics.forEach((stat) => {
            const card = document.createElement("article");
            card.className = "stat-card";

            card.innerHTML = `
                <div class="stat-icon-wrapper">
                    ${stat.icon}
                </div>
                <h3>${stat.title}</h3>
                <p class="stat-number">${stat.value}</p>
            `;

            statisticsContainer.appendChild(card);
        });
    }

    // Mobile Navigation Menu Toggle with Accessibility Support
    if (menuToggle && navigationList) {
        menuToggle.addEventListener("click", () => {
            const isExpanded = navigationList.classList.toggle("active");
            menuToggle.setAttribute("aria-expanded", isExpanded ? "true" : "false");
        });
    }

    // Registration Form Validation & Feedback Handling
    if (registrationForm) {
        registrationForm.addEventListener("submit", async (event) => {
            event.preventDefault();

            const fullName = document.querySelector("#full-name").value.trim();
            const email = document.querySelector("#email").value.trim();
            const studentId = document.querySelector("#student-id").value.trim();
            const department = document.querySelector("#department").value.trim();
            const password = document.querySelector("#password").value;
            const formMessage = document.querySelector("#form-message");

            formMessage.className = ""; // Reset class list

            if (!fullName || !email || !studentId || !department || !password) {
                formMessage.textContent = "Please fill in all required fields.";
                formMessage.classList.add("error");
                return;
            }

            if (!email.includes("@")) {
                formMessage.textContent = "Please enter a valid university email address.";
                formMessage.classList.add("error");
                return;
            }

            if (password.length < 6) {
                formMessage.textContent = "Password must be at least 6 characters long.";
                formMessage.classList.add("error");
                return;
            }

            const submitBtn = registrationForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            formMessage.textContent = "Registering...";

            try {
                const response = await fetch("../../backend/api/register.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        full_name: fullName,
                        email: email,
                        student_id: studentId,
                        department: department,
                        password: password
                    })
                });

                const result = await response.json();

                formMessage.className = "";
                if (response.ok && result.success) {
                    formMessage.textContent = result.message || "Registration successful! Account created.";
                    formMessage.classList.add("success");
                    registrationForm.reset();
                } else {
                    formMessage.textContent = result.message || "Registration failed. Please try again.";
                    formMessage.classList.add("error");
                }
            } catch (err) {
                console.error("Registration error:", err);
                formMessage.className = "error";
                formMessage.textContent = "Unable to connect to server. Please try again.";
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }
});
