function closemessage()
{
    let msg = document.getElementById("message");
    msg.style.display = "none";
} 
function showPwd(id)
{
    let pwd  = document.getElementById(id)
    if(pwd.type == "password"){
        pwd.type = "text";
    }else{
        pwd.type = "password";
    }
}
function buildingSelected()
{
    let bill_building = document.getElementById("bill_building").value;
    window.location.href="bills.php?tab=bills&content=add_bills&building="+bill_building;
}
function buildingAssigned(student_id)
{
    let student_building = document.getElementById("student_building").value;
    window.location.href="students.php?tab=students&content=assign&building="+student_building+"&student="+student_id;
}
function alertingMessage(text, link)
{
    if(confirm(text))
    window.location.href = link;
}
function alertingBoolMessage(text)
{
    if(confirm(text))
    {
        let verification = document.getElementById("verification");
        verification.value = 'yes';
    }
}
function opennavbar()
{
    let nav_links = document.getElementById("responsive_navlinks");
    console.log('clicked');
    let status = window.getComputedStyle(nav_links).display;
    console.log(status);    
    nav_links.classList.toggle("shownav");
}
//start options dropdowns 
let optionsTable = document.querySelectorAll('.options');
let dropdowns = document.querySelectorAll('.dropdown_content');
let wrappers = document.querySelectorAll('.operations_wrapper');
optionsTable.forEach(options => options.addEventListener('click', function(){
    let dropdown_id = 'dropdown'+options.id;
    let wrapper_id = 'wrapper'+options.id;
    let dropdown = document.getElementById(dropdown_id);
    let wrapper = document.getElementById(wrapper_id);
    if(!dropdown.classList.contains('show'))
    {
        let showing = document.querySelectorAll('.show');
        showing.forEach(showingEle => showingEle.classList.remove('show'));
        wrappers.forEach(wrapper=> {
            wrapper.style.backgroundColor='transparent';
            wrapper.style.boxShadow='none';
        });
        dropdown.classList.add('show');
        wrapper.style.backgroundColor='rgba(126, 126, 126, 0.4)';
        wrapper.style.boxShadow = '0 .2em .5em grey';
    }
}))
//end of options dropdowns section
//window onclick
window.onclick = function(e){
    if(!e.target.matches('.options') && !e.target.matches('.dropdown_content'))
    {
        dropdowns.forEach(dropdown => dropdown.classList.remove('show'));
        wrappers.forEach(wrapper=> {
            wrapper.style.backgroundColor='transparent';
            wrapper.style.boxShadow='none';
        });
    }
}
