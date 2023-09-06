// jQuery(document).ready(function ($){
//     const firstItem = $("#carousel").find(".item-card").first();
//     const itemsOnSlide = 4;
//     // $("#carousel").find(".item-card").each(index => $("#carousel").find(".item-card").eq(index).width(100 / itemsOnSlide + "%"));
//     const arrowIcons = $(".items-wrapper").find("span");
//     var isDragStart = false, isDragging = false, prevPageX, prevScrollLeft, positionDiff;

//     const showHideIcons = () => {
//         // showing and hiding prev/next icon according to carousel scroll left value
//         let scrollWidth = $("#carousel")[0].scrollWidth - $("#carousel").innerWidth(); // getting max scrollable width
//         arrowIcons.eq(0).css('display', $("#carousel").scrollLeft() == 0 ? "none" : "block");
//         arrowIcons.eq(1).css('display', $("#carousel").scrollLeft() == Math.floor(scrollWidth) ? "none" : "block");
//     }

//     const handleArrowClick = (e) =>  {
//         let firstItemWidth = firstItem.innerWidth() + 10;
//         let scrolledWidth = $(e.target).attr('id') == "left" ? -firstItemWidth : firstItemWidth;
//         $("#carousel").animate({
//             scrollLeft: "+=" + scrolledWidth
//         }, 0)
//         setTimeout(() => showHideIcons(), 60);
//     }

//     arrowIcons.each(index => {
//         arrowIcons.eq(index).click(handleArrowClick)
//     });

//     // const autoSlide = () => {
//     //     // if there is no image left to scroll then return from here
//     //     if($("#carousel").scrollLeft() - ($("#carousel")[0].scrollWidth - $("#carousel").innerWidth()) > -1 || $("#carousel").scrollLeft() <= 0) return;
    
//     //     positionDiff = Math.abs(positionDiff); // making positionDiff value to positive
//     //     let firstItemWidth = firstItem.innerWidth() + 15;
//     //     // getting difference value that needs to add or reduce from carousel left to take middle img center
//     //     let valDifference = firstItemWidth - positionDiff;
    
//     //     if($("#carousel").scrollLeft() > prevScrollLeft) { // if user is scrolling to the right
//     //         var scrolledWidth = positionDiff > firstItemWidth / 3 ? valDifference : -positionDiff;
//     //         return $("#carousel").animate({
//     //             scrollLeft: "+=" + scrolledWidth
//     //         })
//     //     }
//     //     // if user is scrolling to the left
//     //     var scrolledWidth = positionDiff > firstItemWidth / 3 ? valDifference : -positionDiff;
//     //     $("#carousel").animate({
//     //         scrollLeft: "-=" + scrolledWidth
//     //     })
//     // }


//     const autoSlide = () => {
//         // if there is no image left to scroll then return from here
//         if($("#carousel").scrollLeft() - ($("#carousel")[0].scrollWidth - $("#carousel").innerWidth()) > -1 || $("#carousel").scrollLeft() <= 0) return;
    
//         positionDiff = Math.abs(positionDiff); // making positionDiff value to positive
//         let firstItemWidth = firstItem.innerWidth() + 10;
//         // getting difference value that needs to add or reduce from carousel left to take middle Item center
//         let valDifference = firstItemWidth - positionDiff;
    
//         if($("#carousel").scrollLeft() > prevScrollLeft) { // if user is scrolling to the right
//             return $("#carousel").animate({
//                 scrollLeft: "+=" + positionDiff > firstItemWidth / 3 ? valDifference : -positionDiff
//             });
//         }
//         // if user is scrolling to the left
//         $("#carousel").animate({
//             scrollLeft: "-=" + positionDiff > firstItemWidth / 3 ? valDifference : -positionDiff
//         });
//     }


//     const dragStart = (e) => {
//         // updatating global variables value on mouse down event
//         isDragStart = true;
//         prevPageX = e.pageX || e.touches[0].pageX;
//         prevScrollLeft = $('#carousel').scrollLeft();
//     }
    
//     const dragging = (e) => {
//         // scrolling carousel to left according to mouse pointer
//         if(!isDragStart) return;
//         e.preventDefault();
//         isDragging = true;
//         $("#carousel").addClass("dragging");
//         positionDiff = (e.pageX || e.touches[0].pageX) - prevPageX;


//         var scrolledWidth = prevScrollLeft - positionDiff;

//         $("#carousel").animate({
//             scrollLeft: "+=" + scrolledWidth 
//         }, 10)
        
//         showHideIcons();
//     }

//     const dragStop = () => {
//         isDragStart = false;
//         $("#carousel").removeClass("dragging");
//         if(!isDragging) return;
//         isDragging = false;
//         autoSlide();
//     }

//     $("#carousel").on("mousedown", dragStart);
//     $("#carousel").on("touchstart", dragStart);

//     // document.on("mousemove", dragging);
//     $("#carousel").on("mousemove", dragging);
//     $("#carousel").on("touchmove", dragging);

//     // document.on("mouseup", dragStop);
//     $("#carousel").on("touchend", dragStop);
//     $("#carousel").on("mouseup", dragStop);


// })
const carousel = document.querySelector("#carousel"),
firstItem = carousel.querySelectorAll(".item-card")[0],
allItems = carousel.querySelectorAll('.item-card'),
arrowIcons = document.querySelectorAll(".items-wrapper span"),
shortcodeContainer = document.querySelector("#menu-shortcode"),
bulletsContainer = document.querySelector(".items-wrapper .bullets");

let isDragStart = false, isDragging = false, prevPageX, prevScrollLeft, positionDiff, bulletsNbr;


let id = shortcodeContainer.dataset.id;


let itemsOnSlide = parseInt(SLIDER_OPTIONS.slidesToShow[id]);
let showBullets = SLIDER_OPTIONS.showBullets[id];

console.log(showBullets)

if (showBullets){
    var activeBulletIndex = 0;
    
    let number = window.innerWidth <= 900 ? allItems.length : allItems.length - itemsOnSlide + 1;

    if (window.innerWidth <= 900){
        bulletsNbr = allItems.length;
    }else {
        bulletsNbr = allItems.length - itemsOnSlide + 1;
    }


    window.addEventListener("resize", function () {
        if (window.innerWidth <= 900){
            bulletsNbr = allItems.length;
        }else {
            bulletsNbr = allItems.length - itemsOnSlide + 1;
        }
    })



    while (bulletsNbr > 0 ){
        let span = document.createElement('span');
        if (bulletsNbr === number){
            span.classList.add('active')
        }
        bulletsContainer.appendChild(span);
        bulletsNbr--;
    }
}

allItems.forEach((item, index) => {
    item.style.width = `calc(100% / ${itemsOnSlide} - 5px)`;
});

const showHideIcons = () => {
    let scrollWidth = carousel.scrollWidth - carousel.clientWidth; 
    arrowIcons[0].style.display = carousel.scrollLeft == 0 ? "none" : "block";
    arrowIcons[1].style.display = carousel.scrollLeft == scrollWidth ? "none" : "block";
}

arrowIcons.forEach(icon => {
    icon.addEventListener("click", () => {
        showHideIcons();
        let firstItemWidth = firstItem.clientWidth + 5; 
        carousel.scrollLeft += icon.id == "left" ? -firstItemWidth : firstItemWidth;
        
        // Update the active bullet index
        let bullets = document.querySelector(".items-wrapper .bullets");
        if (bullets){
            const nextActiveBulletIndex = Math.floor(carousel.scrollLeft / firstItemWidth);
            if (nextActiveBulletIndex !== activeBulletIndex) {
                bullets.children[activeBulletIndex].classList.remove('active');
                bullets.children[nextActiveBulletIndex].classList.add('active');
                activeBulletIndex = nextActiveBulletIndex;
            }
        }
    });
});

const autoSlide = () => {
    if(carousel.scrollLeft - (carousel.scrollWidth - carousel.clientWidth) > -1 || carousel.scrollLeft <= 0) return;

    positionDiff = Math.abs(positionDiff);
    let firstItemWidth = firstItem.clientWidth + 5;
    let valDifference = firstItemWidth - positionDiff;

    let bullets = document.querySelector(".items-wrapper .bullets");
    if (bullets){
        const nextActiveBulletIndex = Math.floor(carousel.scrollLeft / firstItemWidth);
        if (nextActiveBulletIndex !== activeBulletIndex) {
            bullets.children[activeBulletIndex].classList.remove('active');
            bullets.children[nextActiveBulletIndex].classList.add('active');
            activeBulletIndex = nextActiveBulletIndex;
        }
    }

    if(carousel.scrollLeft > prevScrollLeft) {
        return carousel.scrollLeft += positionDiff > firstItemWidth / 3 ? valDifference : -positionDiff;
    }
    carousel.scrollLeft -= positionDiff > firstItemWidth / 3 ? valDifference : -positionDiff;
}

const dragStart = (e) => {
    isDragStart = true;
    prevPageX = e.pageX || e.touches[0].pageX;
    prevScrollLeft = carousel.scrollLeft;
}

const dragging = (e) => {
    if(!isDragStart) return;
    e.preventDefault();
    isDragging = true;
    carousel.classList.add("dragging");
    positionDiff = (e.pageX || e.touches[0].pageX) - prevPageX;
    carousel.scrollLeft = prevScrollLeft - positionDiff;
    showHideIcons();
}

const dragStop = () => {
    isDragStart = false;
    carousel.classList.remove("dragging");

    if(!isDragging) return;
    isDragging = false;
    autoSlide();
}

carousel.addEventListener("mousedown", dragStart);
carousel.addEventListener("touchstart", dragStart);

document.addEventListener("mousemove", dragging);
carousel.addEventListener("touchmove", dragging);

document.addEventListener("mouseup", dragStop);
carousel.addEventListener("touchend", dragStop);