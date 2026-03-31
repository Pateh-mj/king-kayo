<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Booking | King Kayo Group</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,900;1,900&family=Plus+Jakarta+Sans:wght@300;400;700;800&display=swap');
        :root { --forest: #042F2C; --gold: #D4AF37; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #FAFAFA; color: var(--forest); }
        .serif-brand { font-family: 'Playfair Display', serif; }
        .form-input { 
            width: 100%; padding: 1rem; background: #fff; border: 1px solid #e5e7eb; 
            outline: none; transition: all 0.3s; 
        }
        .form-input:focus { border-color: var(--gold); ring: 1px solid var(--gold); }
        .hidden-section { display: none; }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <nav class="p-8 flex justify-between items-center bg-white border-b border-zinc-100">
        <div class="serif-brand text-xl font-black text-forest">King~Kayo</div>
        <a href="landing.html" class="text-[10px] font-black uppercase tracking-widest hover:text-gold transition-colors">Return Home</a>
    </nav>

    <main class="flex-grow flex items-center justify-center p-6 lg:p-12">
        <div class="max-w-4xl w-full bg-white shadow-2xl flex flex-col lg:flex-row overflow-hidden">
            
            <div class="lg:w-1/3 bg-forest p-12 text-white flex flex-col justify-between">
                <div>
                    <span class="text-black text-[10px] font-black uppercase tracking-[0.4em] mb-4 block">Reservation</span>
                    <h1 class="serif-brand text-black text-4xl mb-6">Request a Professional Service</h1>
                    <p class="text-zinc-900 text-xs leading-relaxed">
                        Please provide your details and requirements. Our administrative team will review your request and issue a formal quote within 24 hours.
                    </p>
                </div>
                <div>
                    <img src="img/King.png" alt="logo" class="w-50 h-auto mt-8 item-center">
                </div>
                <div class="mt-12 border-t border-white/10 pt-8">
                    <p class="text-[12px] uppercase tracking-widest text-zinc-900 font-bold">Institutional Support</p>
                    <p class="text-[11px] text-zinc-800 mt-2">Specialized handling for Government, Mining, and Private Health sectors.</p>
                </div>
            </div>

            <form action="process_booking.php" method="POST" class="lg:w-2/3 p-12">
                
                <div class="space-y-6">
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-zinc-900 mb-2 block">Full Name / Entity</label>
                            <input type="text" name="name" required class="form-input" placeholder="e.g., John / Ministry of Health">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-zinc-900 mb-2 block">Phone Number</label>
                            <input type="tel" name="phone" required class="form-input" placeholder="+260 ...">
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-zinc-900 mb-2 block">Primary Service Division</label>
                        <select id="service_category" name="service_category" required class="form-input appearance-none" onchange="toggleFields()">
                            <option value="">Select Division...</option>
                            <option value="Medical Waste">Public Health: Medical Waste</option>
                            <option value="Pest Control">Public Health: Pest Control</option>
                            <option value="Fabrication">Engineering: Custom Fabrication</option>
                            <option value="Cleaning">Sanitation: Cleaning Services</option>
                        </select>
                    </div>

                    <div id="section-MedicalWaste" class="hidden-section space-y-4 bg-zinc-50 p-6 border-l-2 border-gold">
                        <label class="text-[10px] font-black uppercase tracking-widest text-forest mb-2 block">Waste Classification</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center space-x-2 text-xs">
                                <input type="checkbox" name="waste_type[]" value="Sharps" class="accent-gold"> <span>Sharps</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs">
                                <input type="checkbox" name="waste_type[]" value="Pathological" class="accent-gold"> <span>Pathological</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs">
                                <input type="checkbox" name="waste_type[]" value="Pharmaceutical" class="accent-gold"> <span>Pharmaceutical</span>
                            </label>
                        </div>
                    </div>

                    <div id="section-Fabrication" class="hidden-section space-y-4 bg-zinc-50 p-6 border-l-2 border-gold">
                        <label class="text-[10px] font-black uppercase tracking-widest text-forest mb-2 block">Fabrication Type</label>
                        <select name="fabrication_type" class="form-input">
                            <option value="Storage Tank">Storage Tank Trailer</option>
                            <option value="Flat Deck">Flat Deck Trailer</option>
                            <option value="Structural">Structural Steel / Weldments</option>
                            <option value="Restoration">Trailer Restoration</option>
                        </select>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-2 block">Preferred Date</label>
                            <input type="date" name="service_date" required class="form-input">
                        </div>
                        <div>
                            <label class="text-[10px] font-black uppercase tracking-widest text-zinc-400 mb-2 block">Area / Province</label>
                            <input type="text" name="location" required class="form-input" placeholder="e.g., Lusaka CBD">
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black uppercase tracking-widest text-zinc-900 mb-2 block">Specific Requirements / Notes</label>
                        <textarea name="notes" rows="3" class="form-input" placeholder="Please describe any unique access requirements or site conditions..."></textarea>
                    </div>

                    <button type="submit" class="w-full bg-black text-white py-4 text-[13px] font-black uppercase tracking-[0.4em] hover:bg-green hover:text-forest transition-all">
                        Request Formal Quote
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer class="p-8 text-center text-zinc-900 text-[10px] uppercase tracking-widest">
        &copy; 2026 King Kayo Group Limited | Our Disability is not our Inability
    </footer>

    <script>
        function toggleFields() {
    const category = document.getElementById('service_category').value;
    
    // Hide everything first
    document.getElementById('section-MedicalWaste').style.display = 'none';
    document.getElementById('section-Fabrication').style.display = 'none';

    // Show only what's relevant
    if (category === 'Medical Waste' || category === 'Pest Control') {
        document.getElementById('section-MedicalWaste').style.display = 'block';
    } else if (category === 'Fabrication') {
        document.getElementById('section-Fabrication').style.display = 'block';
    }
}
    </script>
</body>
</html>