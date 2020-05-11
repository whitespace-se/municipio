export default class ArchiveFilter{

    constructor(){
        this.addListenerToItems();
    }

    addListenerToItems() {
        const taxonomies = document.querySelectorAll('.c-dropdown__list');
        console.log(taxonomies)
        taxonomies.forEach(taxonomy => {
            const categories = taxonomy.querySelectorAll('div');
            
            categories.forEach((category) => {
                
                category.addEventListener('click', (event) => {  
                               
                    const url = new URL(document.URL);
                    const queryParams = url.searchParams;
                    const filter = category.getAttribute('href'); 
                    const filterParts = filter.split('=');
                    const filterKey = filterParts[0];
                    const filterValue = filterParts[1];
    
                    if(queryParams.get(filterKey)) {
                        queryParams.set(filterKey, filterValue)
                    }else{
                        queryParams.append(filterKey, filterValue);
                    }
                    
                    window.location.search = queryParams.toString();
                    event.preventDefault();
                })
            });
        });
    }

}