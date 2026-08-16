/* =================================
   FARMER DIRECT MARKET
   DASHBOARD JS
================================= */


/* ================================
   SIDEBAR ACTIVE MENU
================================ */

const menuLinks = document.querySelectorAll(".menu a");

menuLinks.forEach(link => {

    link.addEventListener("click", function(){

        menuLinks.forEach(item =>
            item.classList.remove("active")
        );

        this.classList.add("active");

    });

});



/* ================================
   DASHBOARD COUNTER ANIMATION
================================ */

const counters =
document.querySelectorAll(".counter");


counters.forEach(counter => {


    let target =
    Number(counter.innerText);


    let count = 0;


    let speed = target / 100;


    let update = () => {


        if(count < target){

            count += speed;

            counter.innerText =
            Math.ceil(count);

            setTimeout(update,20);

        }

        else{

            counter.innerText =
            target;

        }

    };


    update();


});




/* ================================
   PRODUCT DELETE
================================ */


const deleteButtons =
document.querySelectorAll(".delete-btn");


deleteButtons.forEach(btn => {


    btn.addEventListener("click",()=>{


        let confirmDelete =
        confirm(
        "Delete this product?"
        );


        if(confirmDelete){


            let row =
            btn.closest("tr");


            row.remove();


            showAlert(
            "Product deleted successfully"
            );


        }


    });


});





/* ================================
   APPROVE USER / FARMER
================================ */


const approveButtons =
document.querySelectorAll(".approve-btn");


approveButtons.forEach(btn=>{


    btn.addEventListener("click",()=>{


        let status =
        btn.closest("tr")
        .querySelector(".status");


        if(status){


            status.innerText="Active";

            status.className =
            "status active";


            showAlert(
            "User approved"
            );


        }


    });


});





/* ================================
   SEARCH TABLE
================================ */


const dashboardSearch =
document.getElementById(
"dashboardSearch"
);



if(dashboardSearch){


dashboardSearch.addEventListener(
"keyup",
function(){


    let value =
    this.value.toLowerCase();



    let rows =
    document.querySelectorAll(
    "table tbody tr"
    );



    rows.forEach(row=>{


        let text =
        row.innerText.toLowerCase();



        row.style.display =
        text.includes(value)
        ? ""
        : "none";


    });



});

}





/* ================================
   ADD PRODUCT FORM
================================ */


const productForm =
document.getElementById(
"productForm"
);



if(productForm){


productForm.addEventListener(
"submit",
function(e){


    e.preventDefault();


    showAlert(
    "Product added successfully"
    );


    productForm.reset();


});


}






/* ================================
   ORDER STATUS CHANGE
================================ */


const statusButtons =
document.querySelectorAll(
".change-status"
);



statusButtons.forEach(btn=>{


btn.addEventListener(
"click",
()=>{


    let status =
    btn.closest("tr")
    .querySelector(".status");


    status.innerText =
    "Delivered";


    status.className =
    "status delivered";



    showAlert(
    "Order status updated"
    );


});


});







/* ================================
   LOGOUT
================================ */


const logoutBtn =
document.getElementById(
"logoutBtn"
);


if(logoutBtn){


logoutBtn.addEventListener(
"click",
()=>{


    let result =
    confirm(
    "Do you want to logout?"
    );


    if(result){

        window.location.href =
        "login.html";

    }


});


}







/* ================================
   ALERT MESSAGE
================================ */


function showAlert(message){


    let alertBox =
    document.createElement(
    "div"
    );


    alertBox.innerText =
    message;


    alertBox.style.position =
    "fixed";


    alertBox.style.bottom =
    "25px";


    alertBox.style.right =
    "25px";


    alertBox.style.background =
    "#0f9d58";


    alertBox.style.color =
    "white";


    alertBox.style.padding =
    "15px 25px";


    alertBox.style.borderRadius =
    "10px";


    alertBox.style.zIndex =
    "9999";


    document.body.appendChild(
    alertBox
    );


    setTimeout(()=>{

        alertBox.remove();

    },2500);


}






/* ================================
   PAGE LOAD MESSAGE
================================ */


window.addEventListener(
"load",
()=>{


    console.log(
    "Dashboard Loaded Successfully"
    );


});