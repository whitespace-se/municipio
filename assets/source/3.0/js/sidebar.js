let regeneratorRuntime =  require("regenerator-runtime");
export default class Sidebar{

    constructor() {
        this.ATTR = "js-sidebar";
        this.EXPAND = "c-sidebar__item--is-expanded";
        this.EXPANDABLE = "c-sidebar__subcontainer";
        this.ACTIVEITEMS = "active";
        this.ACTIVE = "item-active";
        this.TRIGGER = "js-sidebar-trigger";
        this.URL = "children"
        
        if(localStorage.getItem(this.ACTIVEITEMS) === null){
            localStorage.setItem('active', JSON.stringify({items: []}));
        }
    }

    async loadState() {
        const sb = document.getElementsByClassName('c-sidebar')[0];
        this.URL = sb.getAttribute('child-items-url');
        const activeItems = this.getActiveItems();
        for(const item of activeItems.items) {
            const children = await this.appendChildren(item);  
            const parent = document.querySelector(`[aria-label='${item}']`).parentElement;
            
            parent.appendChild(children)
            this.toggleAriaPressed(document.querySelector(`[aria-label='${item}']`));
            this.addItemTriggers();
        };
    }

    toggleAriaPressed(element) {
        const ariaPressed = element.getAttribute('aria-pressed');
        if(ariaPressed === 'true'){
            element.setAttribute('aria-pressed', 'false');
        }else{
            element.setAttribute('aria-pressed', 'true');
        }
    }

    addItemTriggers() {
        const sb = document.getElementsByClassName('c-sidebar')[0];
        this.URL = sb.getAttribute('child-items-url');
        const sbTriggers = document.getElementsByClassName('c-sidebar__toggle');
        
        for(const trigger of sbTriggers) {
            const hasEventAttached = trigger.getAttribute('toggleEvent');
         
            if(!hasEventAttached){
                trigger.setAttribute('toggleEvent', 'true');
                trigger.addEventListener('click', (e) => {
                    this.toggleAriaPressed(trigger);
                    
                    const label = e.target.getAttribute('aria-label');
                    const parentID = label[0].toLowerCase() + label.substring(1);
                    const parent = document.querySelector(`[aria-label='${parentID}']`).parentElement;

                    this.appendChildren(parentID, e.target.parentElement).then((children) => {
                        if(!this.isAlreadyStored(parentID)) {
                            parent.appendChild(children);
                            this.storeActiveItem(parentID);
                            this.addItemTriggers();
                        }else{
                            this.removeActiveElement(parentID);
                            this.removeActiveItem(parentID);
                        }
                    });
                });
            } 
        };
        
    }
    
    getChildren(parentID) {
        return fetch(location.origin + this.URL + '?parentID=' + parentID)
        .then((response) => {
            return response.json();
        })
        .then((data) => {
            return data;
        });
    }
    
     appendChildren(parentID) {
         return this.getChildren(parentID).then((children) => {
            
            let subContainer = document.createElement('div');
            subContainer.setAttribute('subContainerID', parentID);
            subContainer.classList.add('c-sidebar__subcontainer');
            children.forEach( (child) => {
                const childItem = document.createElement('div');
                childItem.classList.add('c-sidebar__item');
                let link = document.createElement('a');
                link.href = child.href;
                link.classList.add('c-sidebar__link');
                link.text = child.post_title;
                childItem.appendChild(link);
                
                if(Object.keys(child.children).length > 0) {
                    
                    const bar = document.createElement('div');
                    bar.classList.add('bar');

                    let toggle = document.createElement('div');
                    toggle.classList.add('c-sidebar__toggle');
                    toggle.appendChild(bar);
                    toggle.appendChild(bar.cloneNode(true));
                    toggle.setAttribute('aria-label', child.ID);

                    childItem.appendChild(toggle);  
                }
                
                subContainer.appendChild(childItem);
            });
            
            subContainer.classList.add('c-sidebar__item--is-expanded');
            
            return subContainer;
        });

    }
    
    storeActiveItem(item) {
        let activeItems = this.getActiveItems();
        
        activeItems.items.push(item);
        localStorage.setItem(this.ACTIVEITEMS, JSON.stringify(activeItems));
    }

    removeActiveItem(item){
        let activeItems = this.getActiveItems();
        const index = activeItems.items.indexOf(item);

        if (index > -1) {
            activeItems.items.splice(index, 1);
        }

        localStorage.setItem(this.ACTIVEITEMS, JSON.stringify(activeItems));
    }
    
    isAlreadyStored(newItem) {
        let storedItems = this.getActiveItems();
        if(storedItems && storedItems.items){
            for(let i = 0; i < storedItems.items.length ; i++){
                if(storedItems.items[i] === newItem) {
                    return true;
                }
            };
        }
        return false;
    }
    
    getActiveItems() {
        const activeItems = localStorage.getItem(this.ACTIVEITEMS);
        return JSON.parse(activeItems);
    }

    removeActiveElement(label) {
        const element = document.querySelector(`[subContainerID='${label}']`);
        if(element){
            element.parentNode.removeChild(element);
        }
    }
}