const mask_icon = document.querySelector("#mask");
const mask_off = document.querySelector("#mask_off");
const psw_input = document.querySelector(".psw_input");

mask_icon.addEventListener("click", ()=>{
        mask_icon.classList.add('hide');
        mask_off.classList.remove('hide');
        psw_input.type = "text";
});

mask_off.addEventListener("click", ()=>{
        mask_icon.classList.remove('hide');
        mask_off.classList.add('hide');
        psw_input.type = "password";
});
