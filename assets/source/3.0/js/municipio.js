import Fab from './fab';
import SideBar from './sidebar';

const fab = new Fab();
fab.showOnScroll();

const sidebar = new SideBar();
sidebar.loadState();
sidebar.addItemTriggers();