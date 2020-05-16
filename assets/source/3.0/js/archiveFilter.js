export default class ArchiveFilter{

    constructor(){
        this.addListenerToItems();
    }

    addListenerToItems() {
        const taxonomies = document.querySelectorAll('.c-dropdown__list');
    
        taxonomies.forEach(taxonomy => {
            const categories = taxonomy.querySelectorAll('div');
            
            categories.forEach((category) => {
                category.addEventListener('click', (event) => {        
                    const url = new URL(document.URL);
                    const searchParams = url.searchParams;
                    const filter = category.getAttribute('href'); 
                    const filterParts = filter.split('=');
                    const filterKey = filterParts[0];
                    const filterValue = filterParts[1];
               
                    if(filterValue === 'delete') {
                        
                        searchParams.delete(filterKey);
                    }
                    else if(searchParams.get(filterKey)) {
                        searchParams.set(filterKey, filterValue)
                    }else{
                        searchParams.append(filterKey, filterValue);
                    }
                    /* page\/.+?(?=) */

                    const pathName = location.pathname.replace(/page\/.+?(?=)/, 'page/1');
                    searchParams.set('pagination', 1);
                    window.location.href =  pathName + '?' + searchParams.toString();
                    event.preventDefault();
                })
            });
        });
    }

}