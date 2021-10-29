const inputs = document.querySelectorAll('.input')

function focusFunc(){
    let parent = this.parentNode.parentNode;
    parent.classList.add('focus')
}
function blurFunc(){
    let parent = this.parentNode.parentNode;
    if(this.value === '') {
        parent.classList.remove('focus')
    }
}

inputs.forEach((i) => {
    i.addEventListener('focus', focusFunc)
    i.addEventListener('blur', blurFunc)
})

document.addEventListener('load', () => { 
    inputs.forEach((i) => {
        if(i.value !== ""){
            i.value == "";
        }
    })
})
