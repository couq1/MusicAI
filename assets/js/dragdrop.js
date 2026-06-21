// HTML5 Drag & Drop cho trang Beatmaker

document.addEventListener('DOMContentLoaded', () => {
    const soundItems = document.querySelectorAll('.sound-item');
    const slots = document.querySelectorAll('.beatmaker-slot');

    let draggedSound = null;

    // Không cho nút preview bị kéo nhầm
    document.querySelectorAll('.sound-item-play-preview').forEach(btn => {
        btn.setAttribute('draggable', 'false');
    });

    soundItems.forEach(item => {
        // Dòng này rất quan trọng
        item.setAttribute('draggable', 'true');

        item.addEventListener('dragstart', (e) => {
            const soundData = {
                id: item.getAttribute('data-id') || item.getAttribute('data-sound-id'),
                name: item.getAttribute('data-name'),
                category: item.getAttribute('data-category'),
                audio_file: item.getAttribute('data-audio-file')
            };

            if (!soundData.audio_file) {
                console.error('Sound thiếu data-audio-file:', item);
                return;
            }

            draggedSound = soundData;

            e.dataTransfer.effectAllowed = 'copy';
            e.dataTransfer.setData('text/plain', JSON.stringify(soundData));

            item.classList.add('dragging');
        });

        item.addEventListener('dragend', () => {
            item.classList.remove('dragging');
            draggedSound = null;
        });
    });

    slots.forEach(slot => {
        slot.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            slot.classList.add('drag-over');
        });

        slot.addEventListener('dragleave', (e) => {
            if (!slot.contains(e.relatedTarget)) {
                slot.classList.remove('drag-over');
            }
        });

        slot.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();

            slot.classList.remove('drag-over');

            let data = draggedSound;

            if (!data) {
                try {
                    data = JSON.parse(e.dataTransfer.getData('text/plain'));
                } catch (err) {
                    console.error('Drop sound parsing error:', err);
                    return;
                }
            }

            if (!data || !data.audio_file) {
                console.error('Dữ liệu sound không hợp lệ:', data);
                return;
            }

            const slotId = slot.getAttribute('data-slot-id');

            if (!slotId) {
                console.error('Slot thiếu data-slot-id:', slot);
                return;
            }

            document.dispatchEvent(new CustomEvent('sound-dropped', {
                detail: {
                    slotId: slotId,
                    sound: data
                }
            }));
        });
    });
});