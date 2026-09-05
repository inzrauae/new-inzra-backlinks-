/**
 * Command-pattern undo/redo stack. Every mutating operation in the editor
 * (text edit, move, page reorder, rotation, redaction, ...) goes through
 * `push({ do, undo })` rather than mutating the document model directly,
 * so it can be undone/redone uniformly.
 */
export class History {
  constructor(onChange) {
    this.undoStack = [];
    this.redoStack = [];
    this.onChange = onChange || (() => {});
    this.limit = 200;
  }

  push(command) {
    command.do();
    this.undoStack.push(command);
    if (this.undoStack.length > this.limit) this.undoStack.shift();
    this.redoStack.length = 0;
    this.onChange();
  }

  undo() {
    const command = this.undoStack.pop();
    if (!command) return false;
    command.undo();
    this.redoStack.push(command);
    this.onChange();
    return true;
  }

  redo() {
    const command = this.redoStack.pop();
    if (!command) return false;
    command.do();
    this.undoStack.push(command);
    this.onChange();
    return true;
  }

  get canUndo() {
    return this.undoStack.length > 0;
  }

  get canRedo() {
    return this.redoStack.length > 0;
  }

  clear() {
    this.undoStack.length = 0;
    this.redoStack.length = 0;
    this.onChange();
  }
}
