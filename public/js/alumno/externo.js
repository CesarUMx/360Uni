$(document).ready(function () {
    
    
    
    init_select();
    
    init_evaluadores();
    
    
    
    

});


function init_select(){
    
        [].slice.call(document.querySelectorAll('select.cs-select')).forEach(function (el) {
            new SelectFx(el, {
                stickyPlaceholder: false,
                onChange: function (val) {
                    var img = document.createElement('img');
                    img.src = '/img/evaluacion/' + val + '.png';
                    img.onload = function () {
                        
                        el.previousElementSibling.previousElementSibling.style.backgroundImage = 'url(/img/evaluacion/' + val + '.png)';
                        
                        
                        el.parentNode.previousElementSibling.innerHTML=el.children[val].dataset.valor;
                        
                        
                        
                    };
                }
            });
        });
   
}



