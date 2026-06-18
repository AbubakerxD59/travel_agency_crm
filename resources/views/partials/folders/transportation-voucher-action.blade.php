<a href="{{ transportation_voucher_pdf_url() }}"
   target="_blank"
   rel="noopener noreferrer"
   download="{{ transportation_voucher_pdf_filename() }}"
   onclick="(function (link) { const downloadLink = document.createElement('a'); downloadLink.href = link.href; downloadLink.download = link.getAttribute('download') || ''; downloadLink.click(); })(this)"
   class="lead-row-action inline-flex cursor-pointer rounded-lg p-2 text-concierge-muted transition hover:bg-slate-100 hover:text-concierge-navy"
   title="Transportation voucher"
   aria-label="Open and download transportation voucher">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
         stroke-width="1.5" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m12 0V5.625A2.625 2.625 0 0015.375 3H8.625A2.625 2.625 0 006 5.625V14.25m12 0h-2.25M6 14.25h2.25m7.5 0v4.875A2.625 2.625 0 0115.375 21h-1.5a2.625 2.625 0 01-2.53-1.893l-.546-1.94A1.125 1.125 0 0010.125 17.25H8.25a1.125 1.125 0 00-1.122 1.046l-.546 1.94A2.625 2.625 0 014.875 21h-1.5a2.625 2.625 0 01-2.625-2.625V14.25M12 10.5h3.375m-9.75 0H8.25m0 0V8.625m0 3.375v3.375M12 10.5V8.625m0 3.375v3.375M6.75 8.625V6.75a2.25 2.25 0 012.25-2.25h6a2.25 2.25 0 012.25 2.25v1.875" />
    </svg>
</a>
