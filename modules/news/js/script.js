$(() => {
$('.menu-mob-active').click(() => {
$('.box-toggle').slideToggle();
});

$('#slider').slick({
dots: true,
infinite: true,
speed: 300,
rows: 1,
slidesToShow: 1,
slidesToScroll: 1,
slidesPerRow: 3,
arrows: false,
responsive: [
{
//Netbook
breakpoint: 1025,
settings: {
slidesToShow: 1,
slidesToScroll: 1,
slidesPerRow: 2,
rows: 1
}
},
{
//iPad
breakpoint: 1024,
settings: {
slidesToShow: 1,
slidesToScroll: 1,
slidesPerRow: 1,
rows: 1
}
},
{
//Cellulari e minori
breakpoint: 481,
settings: {
slidesToShow: 1,
slidesToScroll: 1,
slidesPerRow: 1,
rows: 1
}
}
]
});

});