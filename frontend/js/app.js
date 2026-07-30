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

    const menuToggle = document.querySelector("#menu-toggle");
    const navigationList = document.querySelector(".main-nav ul");

    if (menuToggle && navigationList) {

        menuToggle.addEventListener("click", () => {
            navigationList.classList.toggle("active");
        });

    }

});
