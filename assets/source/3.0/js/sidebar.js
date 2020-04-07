let regeneratorRuntime =  require("regenerator-runtime");
export default class Sidebar{

    constructor() {
        this.ATTR = "js-sidebar";
        this.EXPAND = "c-sidebar__item--is-expanded";
        this.EXPANDABLE = "c-sidebar__subcontainer";
        this.TOGGLEDITEMS = 'toggled';
        this.ACTIVEITEMS = 'active'
        this.ACTIVE = "item-active";
        this.TRIGGER = "js-sidebar-trigger";
        this.URL = "children"
        
        if(localStorage.getItem(this.TOGGLEDITEMS) === null){
            localStorage.setItem(this.TOGGLEDITEMS, JSON.stringify({items: []}));
        }
        
        if(localStorage.getItem(this.ACTIVEITEMS) === null){
            localStorage.setItem(this.ACTIVEITEMS, JSON.stringify({items: []}));
        }
    }

    async loadState() {
        console.log("LOAD");
        
        const sb = document.getElementsByClassName('c-sidebar')[0];
        this.URL = sb.getAttribute('child-items-url');
        const activeItems = this.getItems(this.TOGGLEDITEMS);
        for(const item of activeItems.items) {
            const children = await this.appendChildren(item);  
            const parent = document.querySelector(`[aria-label='${item}']`).parentElement;
            
            parent.appendChild(children)
            console.log(parent)
            this.toggleAriaPressed(document.querySelector(`[aria-label='${item}']`));
            this.addItemTriggers();
        };
        console.log('WHAT')
        this.markActiveTree();
    }

    toggleAriaPressed(element) {
        const ariaPressed = element.getAttribute('aria-pressed');
        if(ariaPressed === 'true'){
            element.setAttribute('aria-pressed', 'false');
        }else{
            element.setAttribute('aria-pressed', 'true');
        }
    }

    addLinkTrigger(link) {
        link.addEventListener('click', (event) => {
            const linkID = link.id;
            
            this.storeItem(linkID, this.ACTIVEITEMS);
        });
    }

    markActiveTree() {
        console.log('MARK');
        const activeLinkID = this.getItems(this.ACTIVEITEMS).items[0];
        console.log(activeLinkID);
        let traversing = true;

        const currentLink = document.getElementById(activeLinkID)
        const item = currentLink.parentElement;
        const subContainer = item.parentElement;
        const parentLink = subContainer.parentElement;

        console.log('currentItem')
        console.log(currentItem)

        while(traversing) {
            
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
                            this.storeItem(parentID, this.TOGGLEDITEMS);
                            this.addItemTriggers();
                        }else{
                            this.removeToggledElement(parentID);
                            this.removeToggledItem(parentID);
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
            
            let linkIndex = 0;
            children.forEach( (child) => {
                const childItem = document.createElement('div');
                childItem.classList.add('c-sidebar__item');
                let link = document.createElement('a');
                link.href = "#";
                link.classList.add('c-sidebar__link');
                link.text = child.post_title;
                link.setAttribute('item-active', 'false');
                link.id = parentID + '-' + linkIndex;
                linkIndex ++;

                this.addLinkTrigger(link, parentID);
                
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
    
    
    
    isAlreadyStored(newItem) {
        let storedItems = this.getItems(this.TOGGLEDITEMS);
        if(storedItems && storedItems.items){
            for(let i = 0; i < storedItems.items.length ; i++){
                if(storedItems.items[i] === newItem) {
                    return true;
                }
            };
        }
        return false;
    }
    
    storeItem(item, itemState) {
        let activeItems = this.getItems(itemState);
        
        activeItems.items.push(item);
        localStorage.setItem(itemState, JSON.stringify(activeItems));
    }
    
    removeToggledItem(item){
        let activeItems = this.getItems(this.TOGGLEDITEMS);
        const index = activeItems.items.indexOf(item);
        
        if (index > -1) {
            activeItems.items.splice(index, 1);
        }
        
        localStorage.setItem(this.TOGGLEDITEMS, JSON.stringify(activeItems));
    }

    getItems(itemState) {
        console.log(itemState)
        const items = localStorage.getItem(itemState);
        console.log(items)
        return JSON.parse(items);
    }
    
    removeToggledElement(label) {
        const element = document.querySelector(`[subContainerID='${label}']`);
        if(element){
            element.parentNode.removeChild(element);
        }
    }
}