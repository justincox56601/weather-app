const dropdowns = document.querySelectorAll('.dropdown-container');

if(dropdowns.length > 0){
	dropdowns.forEach(d=>{
		const heading = d.querySelector('.dropdown-heading');
		const child = d.querySelector('.dropdown');
		heading.addEventListener('click', e=>{
			//reset all of them
			dropdowns.forEach(drop=>{
				if(drop == d){return}
				drop.querySelector('.dropdown').style.height = 0;
				drop.querySelector('.dropdown').style.marginBottom = 0;
			});

			if(child.style.height < child.scrollHeight + 'px'){
				child.style.height = child.scrollHeight + 'px';
				child.style.marginBottom = '1rem';
			}else{
				child.style.height = 0;
				child.style.marginBottom = 0;
			}
			
		})
	})
}