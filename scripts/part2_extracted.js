
        // Simple global helper so inline onclick can always open modal
        window.openDeleteModal = function(form){
            try{
                window.__pendingDeleteForm = form;
                const modal = document.getElementById('delete-confirm-modal');
                const which = document.getElementById('delete-confirm-which2');
                const input = document.getElementById('delete-confirm-input2');
                if(which){
                    const name = form?.querySelector('td:nth-child(2) > div')?.textContent?.trim() || '';
                    const email = form?.querySelector('td:nth-child(2) > div + div')?.textContent?.trim() || '';
                    which.textContent = (name ? name + ' ' : '') + (email ? ('(' + email + ')') : '');
                }
                if(input) input.value = '';
                const doBtn = document.getElementById('delete-confirm-do2'); if(doBtn) doBtn.disabled = true;
                if(modal){ modal.style.display = 'flex'; modal.classList.add('show'); }
                if(input) input.focus();
            }catch(e){ console.error('openDeleteModal error', e); }
            return false;
        };

        // Modal-driven delete confirmation
        document.addEventListener('DOMContentLoaded', function(){
            function getRowNameEmail(form){
                try{
                    const tr = form.closest('tr');
                    if(!tr) return {name:'', email:''};
                    const nameEl = tr.querySelector('td:nth-child(2) > div');
                    const emailEl = tr.querySelector('td:nth-child(2) > div + div');
                    const name = nameEl ? nameEl.textContent.trim() : '';
                    const email = emailEl ? emailEl.textContent.trim() : '';
                    return {name, email};
                }catch(e){ return {name:'', email:''}; }
            }

                    // Modal-driven delete confirmation (simplified)
                    (function(){
                        const modal = document.getElementById('delete-confirm-modal');
                        const which = document.getElementById('delete-confirm-which2');
                        const input = document.getElementById('delete-confirm-input2');
                        const doBtn = document.getElementById('delete-confirm-do2');
                        const cancel = document.getElementById('delete-confirm-cancel2');
                        const closeBtn = document.getElementById('delete-confirm-close');
                        let pendingForm = null;

                        function openModal(form){
                            const info = getRowNameEmail(form);
                            if(which) which.textContent = (info.name ? info.name + ' ' : '') + (info.email ? ('(' + info.email + ')') : '');
                            if(input) input.value = '';
                            if(doBtn) doBtn.disabled = true;
                            pendingForm = form;
                            if(modal){ modal.style.display = 'flex'; modal.classList.add('show'); }
                            if(input) input.focus();
                        }

                        function closeModal(){ if(modal){ modal.style.display = 'none'; modal.classList.remove('show'); } pendingForm = null; try{ window.__pendingDeleteForm = null; }catch(e){} }

                        document.querySelectorAll('form.delete-account-form').forEach(function(form){
                            form.addEventListener('submit', function(e){
                                e.preventDefault();
                                openModal(form);
                            });
                        });

                        if(input) input.addEventListener('input', function(){ if(doBtn) doBtn.disabled = (this.value || '').trim() !== 'CONFIRM'; });

                        if(doBtn){
                            doBtn.addEventListener('click', function(e){
                                e.preventDefault();
                                const formToSubmit = pendingForm || window.__pendingDeleteForm;
                                if(!formToSubmit) return closeModal();
                                try{ formToSubmit.submit(); }catch(err){ console.error('submit failed', err); }
                                closeModal();
                            });
                        }

                        if(cancel) cancel.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });
                        if(closeBtn) closeBtn.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });
                        window.addEventListener('keyup', function(e){ if(e.key === 'Escape') closeModal(); });
                    })();
                });
        