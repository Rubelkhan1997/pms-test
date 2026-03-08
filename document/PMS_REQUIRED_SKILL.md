1) মূল পার্থক্য
--------------------------------------------------------------------
বিষয়	           পুরাতন (js2)	            নতুন (js)
--------------------------------------------------------------------
Vue Version	     Vue 2	                         Vue 3
Routing	         Vue Router (explicit)	        Inertia (Laravel routes)
State	         localStorage / EventBus	    Pinia Stores
Logic            Reuse	Mixins / Methods	    Composables (useXxx)
Auth Guard	     Router beforeEach	            Laravel Middleware
API Config	     config.js (window expose)      Composables + axios
Type Safety	     JavaScript	                    TypeScript


2) Learning Roadmap
--------------------------------------------------------------------
Week 1: Vue 3 Composition API (ref, computed, watch, script setup)
Week 2: Inertia.js (routing, forms, props)
Week 3: Pinia (state management)
Week 4: Composables pattern + TypeScript basics