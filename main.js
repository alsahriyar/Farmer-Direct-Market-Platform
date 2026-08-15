/* =========================
   MOBILE MENU TOGGLE
========================= */

const menuBtn = document.querySelector(".menu-btn");
const navLinks = document.querySelector(".nav-links");

if(menuBtn && navLinks){

    menuBtn.addEventListener("click", () => {
        navLinks.classList.toggle("active");
    });

}

/* =========================
   SEARCH FILTER
========================= */

const searchInput = document.getElementById("searchInput");
const productCards = document.querySelectorAll(".product-card");

if(searchInput){

    searchInput.addEventListener("keyup", () => {

        let value = searchInput.value.toLowerCase();

        productCards.forEach(card => {

            let text = card.innerText.toLowerCase();

            if(text.includes(value)){
                card.style.display = "block";
            }else{
                card.style.display = "none";
            }

        });

    });

}

/* =========================
   CART COUNTER
========================= */

let cartCount = localStorage.getItem("cartCount") || 0;

const cartBadge = document.getElementById("cartCount");

if(cartBadge){
    cartBadge.innerText = cartCount;
}

const addToCartBtns = document.querySelectorAll(".add-to-cart");

addToCartBtns.forEach(btn => {

    btn.addEventListener("click", () => {

        cartCount++;

        localStorage.setItem("cartCount", cartCount);

        if(cartBadge){
            cartBadge.innerText = cartCount;
        }

        alert("Product added to cart!");
    });

});

/* =========================
   CONTACT FORM
========================= */

const contactForm = document.getElementById("contactForm");

if(contactForm){

    contactForm.addEventListener("submit", (e) => {

        e.preventDefault();

        alert("Message sent successfully!");

        contactForm.reset();

    });

}

/* =========================
   LOGIN FORM
========================= */

const loginForm = document.getElementById("loginForm");

if(loginForm){

    loginForm.addEventListener("submit", (e) => {

        e.preventDefault();

        const email =
        document.getElementById("email").value;

        const password =
        document.getElementById("password").value;

        if(email === "" || password === ""){
            alert("Please fill all fields");
            return;
        }

        alert("Login Successful");

    });

}

/* =========================
   REGISTER FORM
========================= */

const registerForm =
document.getElementById("registerForm");

if(registerForm){

    registerForm.addEventListener("submit",(e)=>{

        e.preventDefault();

        const password =
        document.getElementById("password").value;

        const confirmPassword =
        document.getElementById("confirmPassword").value;

        if(password !== confirmPassword){

            alert("Passwords do not match");
            return;
        }

        alert("Registration Successful");

    });

}

/* =========================
   DASHBOARD TABLE SEARCH
========================= */

const tableSearch =
document.getElementById("tableSearch");

if(tableSearch){

    tableSearch.addEventListener("keyup", function(){

        let value =
        this.value.toLowerCase();

        let rows =
        document.querySelectorAll("tbody tr");

        rows.forEach(row => {

            row.style.display =
            row.innerText.toLowerCase().includes(value)
            ? ""
            : "none";

        });

    });

}

/* =========================
   PRODUCT DELETE
========================= */

const deleteBtns =
document.querySelectorAll(".delete-btn");

deleteBtns.forEach(btn => {

    btn.addEventListener("click", () => {

        let confirmDelete =
        confirm("Are you sure?");

        if(confirmDelete){

            btn.closest("tr").remove();

        }

    });

});

/* =========================
   PRICE TREND FILTER
========================= */

const cropFilter =
document.getElementById("cropFilter");

if(cropFilter){

    cropFilter.addEventListener("change", () => {

        console.log(
        "Selected Crop:",
        cropFilter.value
        );

    });

}

/* =========================
   DARK MODE
========================= */

const darkModeBtn =
document.getElementById("darkModeBtn");

if(darkModeBtn){

    darkModeBtn.addEventListener("click", () => {

        document.body.classList.toggle("dark-mode");

        localStorage.setItem(
            "darkMode",
            document.body.classList.contains("dark-mode")
        );

    });

}

if(localStorage.getItem("darkMode") === "true"){

    document.body.classList.add("dark-mode");

}

/* =========================
   CURRENT YEAR
========================= */

const currentYear =
document.getElementById("currentYear");

if(currentYear){

    currentYear.innerText =
    new Date().getFullYear();

}