/*
====================================================
Mtell Online Shopping
Main JavaScript
====================================================
*/

document.addEventListener("DOMContentLoaded", function () {

    /*
    ========================================
    Mobile Menu
    ========================================
    */

    const menuBtn = document.querySelector(".mobile-toggle");
    const mobileMenu = document.querySelector(".mobile-menu");
    const overlay = document.querySelector(".mobile-overlay");

    if(menuBtn && mobileMenu){

        menuBtn.addEventListener("click", function(){

            mobileMenu.classList.add("active");

            if(overlay){
                overlay.classList.add("active");
            }

        });

    }

    if(overlay){

        overlay.addEventListener("click", function(){

            mobileMenu.classList.remove("active");
            overlay.classList.remove("active");

        });

    }

    /*
    ========================================
    Back To Top
    ========================================
    */

    const topButton = document.getElementById("backToTop");

    window.addEventListener("scroll", function(){

        if(!topButton) return;

        if(window.scrollY > 300){

            topButton.style.display = "flex";

        }else{

            topButton.style.display = "none";

        }

    });

    if(topButton){

        topButton.addEventListener("click", function(){

            window.scrollTo({

                top:0,

                behavior:"smooth"

            });

        });

    }

    /*
    ========================================
    Hero Slider
    ========================================
    */

    const slides = document.querySelectorAll(".hero-slide");

    let currentSlide = 0;

    function showSlide(index){

        if(slides.length===0) return;

        slides.forEach(function(slide){

            slide.classList.remove("active");

        });

        slides[index].classList.add("active");

    }

    if(slides.length){

        showSlide(0);

        setInterval(function(){

            currentSlide++;

            if(currentSlide>=slides.length){

                currentSlide=0;

            }

            showSlide(currentSlide);

        },5000);

    }

    /*
    ========================================
    Smooth Scroll
    ========================================
    */

    document.querySelectorAll('a[href^="#"]').forEach(function(anchor){

        anchor.addEventListener("click", function(e){

            const target=document.querySelector(this.getAttribute("href"));

            if(target){

                e.preventDefault();

                target.scrollIntoView({

                    behavior:"smooth"

                });

            }

        });

    });

    /*
    ========================================
    Product Hover Animation
    ========================================
    */

    const cards=document.querySelectorAll(".product-card");

    cards.forEach(function(card){

        card.addEventListener("mouseenter",function(){

            card.classList.add("shadow-lg");

        });

        card.addEventListener("mouseleave",function(){

            card.classList.remove("shadow-lg");

        });

    });

    /*
    ========================================
    Toast Message
    ========================================
    */

    window.showToast=function(message,color="#2e7d32"){

        let toast=document.querySelector(".toast");

        if(!toast){

            toast=document.createElement("div");

            toast.className="toast";

            document.body.appendChild(toast);

        }

        toast.innerHTML=message;

        toast.style.background=color;

        toast.style.display="block";

        toast.style.opacity="1";

        setTimeout(function(){

            toast.style.opacity="0";

            setTimeout(function(){

                toast.style.display="none";

            },400);

        },3000);

    };

    /*
    ========================================
    Newsletter Validation
    ========================================
    */

    const newsletter=document.querySelector(".newsletter form");

    if(newsletter){

        newsletter.addEventListener("submit",function(e){

            const email=this.querySelector("input[type=email]");

            if(email.value.trim()===""){

                e.preventDefault();

                showToast("Please enter your email","#e53935");

            }

        });

    }

    /*
    ========================================
    Quantity Buttons
    ========================================
    */

    document.querySelectorAll(".qty-plus").forEach(function(btn){

        btn.addEventListener("click",function(){

            const input=this.parentElement.querySelector("input");

            input.value=parseInt(input.value)+1;

        });

    });

    document.querySelectorAll(".qty-minus").forEach(function(btn){

        btn.addEventListener("click",function(){

            const input=this.parentElement.querySelector("input");

            let value=parseInt(input.value);

            if(value>1){

                input.value=value-1;

            }

        });

    });

    /*
    ========================================
    Countdown Timer
    ========================================
    */

    const countdown=document.getElementById("flashCountdown");

    if(countdown){

        let hours=23;
        let minutes=59;
        let seconds=59;

        setInterval(function(){

            seconds--;

            if(seconds<0){

                seconds=59;
                minutes--;

            }

            if(minutes<0){

                minutes=59;
                hours--;

            }

            if(hours<0){

                hours=23;

            }

            countdown.innerHTML=

                hours.toString().padStart(2,"0")+

                ":"+

                minutes.toString().padStart(2,"0")+

                ":"+

                seconds.toString().padStart(2,"0");

        },1000);

    }

    /*
    ========================================
    Search Box Focus
    ========================================
    */

    const search=document.querySelector(".search-area input");

    if(search){

        search.addEventListener("focus",function(){

            this.parentElement.style.boxShadow="0 0 10px rgba(21,101,192,.3)";

        });

        search.addEventListener("blur",function(){

            this.parentElement.style.boxShadow="none";

        });

    }

    /*
    ========================================
    Lazy Loading Images
    ========================================
    */

    const lazyImages=document.querySelectorAll("img[data-src]");

    if("IntersectionObserver" in window){

        const observer=new IntersectionObserver(function(entries){

            entries.forEach(function(entry){

                if(entry.isIntersecting){

                    const img=entry.target;

                    img.src=img.dataset.src;

                    img.removeAttribute("data-src");

                    observer.unobserve(img);

                }

            });

        });

        lazyImages.forEach(function(img){

            observer.observe(img);

        });

    }

});