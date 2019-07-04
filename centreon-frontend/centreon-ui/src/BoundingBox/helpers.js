const normalize = rectangle => {
  if (rectangle.width === undefined) {
    rectangle.width = rectangle.right - rectangle.left;
  }

  if (rectangle.height === undefined) {
    rectangle.height = rectangle.bottom - rectangle.top;
  }

  return rectangle;
};

export { normalize };
