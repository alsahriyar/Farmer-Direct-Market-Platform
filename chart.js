/* =========================
   FARMER DIRECT MARKET
   CHART JS FILE
========================= */


/* =========================
   CROP PRICE TREND CHART
========================= */

const priceChart =
document.getElementById("priceChart");


if(priceChart){

    new Chart(priceChart, {

        type:"line",

        data:{

            labels:[
                "Mon",
                "Tue",
                "Wed",
                "Thu",
                "Fri",
                "Sat",
                "Sun"
            ],

            datasets:[

                {
                    label:"Rice Price (৳/kg)",

                    data:[
                        50,
                        52,
                        51,
                        54,
                        56,
                        57,
                        58
                    ],

                    borderWidth:3,

                    tension:.4
                },


                {
                    label:"Tomato Price (৳/kg)",

                    data:[
                        30,
                        32,
                        35,
                        34,
                        36,
                        38,
                        40
                    ],

                    borderWidth:3,

                    tension:.4
                },


                {
                    label:"Potato Price (৳/kg)",

                    data:[
                        35,
                        34,
                        33,
                        34,
                        33,
                        32,
                        32
                    ],

                    borderWidth:3,

                    tension:.4
                }

            ]

        },


        options:{

            responsive:true,

            plugins:{

                legend:{
                    position:"top"
                },

                title:{

                    display:true,

                    text:"Weekly Crop Market Price"

                }

            }

        }

    });

}





/* =========================
   ADMIN SALES CHART
========================= */


const salesChart =
document.getElementById("salesChart");


if(salesChart){


    new Chart(salesChart,{

        type:"bar",

        data:{

            labels:[

                "Jan",
                "Feb",
                "Mar",
                "Apr",
                "May",
                "Jun"

            ],

            datasets:[{

                label:"Monthly Sales",

                data:[

                    12000,
                    18000,
                    15000,
                    25000,
                    22000,
                    30000

                ],

                borderWidth:1

            }]

        },


        options:{

            responsive:true

        }

    });

}





/* =========================
   ORDER STATUS PIE CHART
========================= */


const orderChart =
document.getElementById("orderChart");


if(orderChart){


    new Chart(orderChart,{

        type:"pie",

        data:{

            labels:[

                "Delivered",
                "Pending",
                "Processing"

            ],


            datasets:[{

                data:[

                    70,
                    15,
                    15

                ],

                borderWidth:1

            }]

        },


        options:{

            responsive:true

        }

    });

}





/* =========================
   FARMER EARNING CHART
========================= */


const earningChart =
document.getElementById("earningChart");


if(earningChart){


    new Chart(earningChart,{

        type:"line",

        data:{

            labels:[

                "Week 1",
                "Week 2",
                "Week 3",
                "Week 4"

            ],


            datasets:[{

                label:"Earnings",

                data:[

                    5000,
                    8000,
                    6500,
                    12000

                ],

                borderWidth:3,

                tension:.4

            }]

        },


        options:{

            responsive:true

        }

    });

}