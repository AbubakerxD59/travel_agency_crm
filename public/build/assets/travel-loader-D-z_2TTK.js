const n=`
    <div class="travel-loader__scene" aria-hidden="true">
        <div class="travel-loader__cloud travel-loader__cloud--1"></div>
        <div class="travel-loader__cloud travel-loader__cloud--2"></div>
        <div class="travel-loader__globe">
            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="24" cy="24" r="20" stroke="currentColor" stroke-width="2" opacity="0.35"></circle>
                <ellipse cx="24" cy="24" rx="8" ry="20" stroke="currentColor" stroke-width="1.5" opacity="0.5"></ellipse>
                <path d="M4 24h40" stroke="currentColor" stroke-width="1.5" opacity="0.45"></path>
                <path d="M8 14c8 4 24 4 32 0M8 34c8-4 24-4 32 0" stroke="currentColor" stroke-width="1.5" opacity="0.35" stroke-linecap="round"></path>
            </svg>
        </div>
        <div class="travel-loader__orbit">
            <svg class="travel-loader__plane" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M20.5 11.5L4 6.5l2.5 5.5-2 2 3.5 1 2 5.5 2-1.5 2 1.5 2-5.5 3.5-1-2-2 2.5-5.5z" fill="currentColor"></path>
            </svg>
        </div>
    </div>`;function i(e){return String(e).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;")}function s(e="md",t=""){const r=e==="sm"||e==="lg"?`travel-loader--${e}`:"travel-loader--md",a=t||"Loading",d=t&&e!=="sm"?`<p class="travel-loader__message">${i(t)}</p>`:"";return`<div class="travel-loader ${r}" role="status" aria-live="polite" aria-busy="true" aria-label="${i(a)}">${n}${d}</div>`}function c(e,t){if(e instanceof HTMLButtonElement){if(t){if(e.dataset.loading==="1")return;e.dataset.loading="1",e.dataset.originalHtml=e.innerHTML,e.disabled=!0,e.innerHTML=s("sm");return}e.dataset.originalHtml&&(e.innerHTML=e.dataset.originalHtml),delete e.dataset.originalHtml,delete e.dataset.loading,e.disabled=!1}}const v="travel-loader-overlay";let l=0;function o(){return document.getElementById(v)}function u(e="Loading…"){const t=o();if(!t)return;const r=t.querySelector(".travel-loader__message");r&&(r.textContent=e),l+=1,t.classList.remove("pointer-events-none","invisible","opacity-0"),t.classList.add("pointer-events-auto","visible","opacity-100"),t.setAttribute("aria-hidden","false"),document.body.classList.add("overflow-hidden")}function p(){const e=o();e&&(l=Math.max(0,l-1),!(l>0)&&(e.classList.add("pointer-events-none","invisible","opacity-0"),e.classList.remove("pointer-events-auto","visible","opacity-100"),e.setAttribute("aria-hidden","true"),document.body.classList.remove("overflow-hidden")))}function h(e,t="Loading…",r="md"){if(!(e instanceof HTMLElement))return;e.classList.add("travel-loader-host--active");let a=e.querySelector(":scope > .travel-loader-host__layer");a||(a=document.createElement("div"),a.className="travel-loader-host__layer",e.appendChild(a)),a.innerHTML=s(r,t),a.classList.remove("hidden"),a.setAttribute("aria-hidden","false"),e.setAttribute("aria-busy","true")}function m(e){if(!(e instanceof HTMLElement))return;const t=e.querySelector(":scope > .travel-loader-host__layer");t?.classList.add("hidden"),t?.setAttribute("aria-hidden","true"),e.classList.remove("travel-loader-host--active"),e.removeAttribute("aria-busy")}function f(e,t="md",r=""){e instanceof HTMLElement&&(e.innerHTML=s(t,r),e.classList.remove("hidden"),e.setAttribute("aria-busy","true"))}const y={loaderHtml:s,setButtonLoading:c,showOverlay:u,hideOverlay:p,showInContainer:h,hideFromContainer:m,renderInto:f};export{y as T,h as a,m as h,c as s};
